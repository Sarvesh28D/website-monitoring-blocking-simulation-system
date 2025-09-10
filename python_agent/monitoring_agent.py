#!/usr/bin/env python3
"""
Monitoring Agent Module
======================

Main monitoring agent that coordinates user simulation and logging
for the Website Monitoring & Blocking Simulation System.

Author: Expert Software Engineer
Created: September 10, 2025
Version: 1.0
"""

import json
import os
import random
import time
import logging
import signal
from datetime import datetime, timedelta
from typing import Dict
from concurrent.futures import ThreadPoolExecutor

from database_manager import DatabaseManager
from website_generator import WebsiteGenerator
from user_simulator import UserSimulator

# Configure logging
logger = logging.getLogger(__name__)

class MonitoringAgent:
    """
    Main monitoring agent that coordinates user simulation and logging
    """
    
    def __init__(self, config_file: str = 'config.json'):
        """
        Initialize the monitoring agent
        
        Args:
            config_file: Path to configuration file
        """
        self.config = self._load_config(config_file)
        self.db_manager = DatabaseManager(self.config['database'])
        self.website_generator = WebsiteGenerator()
        self.user_simulators = {}
        self.is_running = False
        
        # Statistics tracking
        self.stats = {
            'total_visits': 0,
            'blocked_visits': 0,
            'allowed_visits': 0,
            'errors': 0
        }
        
        # Setup signal handlers for graceful shutdown
        signal.signal(signal.SIGINT, self._signal_handler)
        signal.signal(signal.SIGTERM, self._signal_handler)
        
    def _load_config(self, config_file: str) -> Dict:
        """
        Load configuration from JSON file or use defaults
        
        Args:
            config_file: Path to config file
            
        Returns:
            Configuration dictionary
        """
        default_config = {
            'database': {
                'host': 'localhost',
                'database': 'website_monitoring',
                'user': 'root',
                'password': '',
                'charset': 'utf8mb4',
                'autocommit': True
            },
            'simulation': {
                'num_users': 5,
                'min_visit_interval': 1,
                'max_visit_interval': 10,
                'max_runtime_hours': 24
            }
        }
        
        if os.path.exists(config_file):
            try:
                with open(config_file, 'r') as f:
                    loaded_config = json.load(f)
                    # Merge with defaults
                    default_config.update(loaded_config)
                    logger.info(f"Configuration loaded from {config_file}")
            except Exception as e:
                logger.warning(f"Failed to load config file {config_file}: {e}")
                logger.info("Using default configuration")
        else:
            logger.info("No config file found, using default configuration")
            # Save default config for user reference
            self._save_config(config_file, default_config)
            
        return default_config
    
    def _save_config(self, config_file: str, config: Dict) -> None:
        """
        Save configuration to file
        
        Args:
            config_file: Path to save config
            config: Configuration dictionary
        """
        try:
            with open(config_file, 'w') as f:
                json.dump(config, f, indent=4)
            logger.info(f"Default configuration saved to {config_file}")
        except Exception as e:
            logger.error(f"Failed to save config file: {e}")
    
    def _signal_handler(self, signum, frame):
        """Handle shutdown signals gracefully"""
        logger.info(f"Received signal {signum}, shutting down gracefully...")
        self.stop()
    
    def _initialize_users(self) -> None:
        """Initialize user simulators"""
        num_users = self.config['simulation']['num_users']
        
        for user_id in range(1, num_users + 1):
            self.user_simulators[user_id] = UserSimulator(user_id)
            
        logger.info(f"Initialized {num_users} user simulators")
    
    def log_website_visit(self, user_id: int, site_name: str, status: str, 
                         user_agent: str = None, ip_address: str = None) -> bool:
        """
        Log a website visit to the database
        
        Args:
            user_id: ID of the user
            site_name: Name of the website visited
            status: 'allowed' or 'blocked'
            user_agent: Browser user agent string
            ip_address: User's IP address
            
        Returns:
            True if logged successfully, False otherwise
        """
        try:
            query = """
                INSERT INTO sites_visited (user_id, url, status, user_agent, ip_address)
                VALUES (%s, %s, %s, %s, %s)
            """
            params = (user_id, site_name, status, user_agent, ip_address)
            
            self.db_manager.execute_query(query, params)
            
            # Update statistics
            self.stats['total_visits'] += 1
            if status == 'blocked':
                self.stats['blocked_visits'] += 1
            else:
                self.stats['allowed_visits'] += 1
                
            logger.debug(f"User {user_id} visit to {site_name}: {status}")
            return True
            
        except Exception as e:
            logger.error(f"Failed to log visit for user {user_id} to {site_name}: {e}")
            self.stats['errors'] += 1
            return False
    
    def simulate_user_session(self, user_id: int) -> None:
        """
        Simulate a browsing session for a specific user
        
        Args:
            user_id: ID of the user to simulate
        """
        if not self.is_running:
            return
            
        user_simulator = self.user_simulators[user_id]
        
        try:
            # Generate a website visit
            visit_data = user_simulator.generate_visit(self.website_generator)
            site_name = visit_data['site_name']
            
            # Check if site is blocked
            is_blocked = self.website_generator.is_site_blocked(site_name, self.db_manager)
            status = 'blocked' if is_blocked else 'allowed'
            
            # Log the visit
            success = self.log_website_visit(
                user_id=user_id,
                site_name=site_name,
                status=status,
                user_agent=visit_data['user_agent'],
                ip_address=visit_data['ip_address']
            )
            
            if success:
                action = "blocked from" if is_blocked else "visited"
                logger.info(f"User {user_id} {action} {site_name}")
            
        except Exception as e:
            logger.error(f"Error in user session simulation for user {user_id}: {e}")
            self.stats['errors'] += 1
    
    def run_continuous_simulation(self) -> None:
        """
        Run continuous simulation with multiple users
        """
        logger.info("Starting continuous website monitoring simulation")
        self.is_running = True
        
        # Initialize blocked sites cache
        self.website_generator.update_blocked_sites_cache(self.db_manager)
        
        # Start time tracking
        start_time = datetime.now()
        max_runtime = timedelta(hours=self.config['simulation']['max_runtime_hours'])
        
        # Statistics reporting interval
        last_stats_report = datetime.now()
        stats_interval = timedelta(minutes=5)
        
        with ThreadPoolExecutor(max_workers=len(self.user_simulators)) as executor:
            while self.is_running and (datetime.now() - start_time) < max_runtime:
                try:
                    # Submit simulation tasks for all users
                    futures = []
                    for user_id in self.user_simulators.keys():
                        if self.is_running:
                            future = executor.submit(self.simulate_user_session, user_id)
                            futures.append(future)
                    
                    # Wait for all tasks to complete
                    for future in futures:
                        try:
                            future.result(timeout=10)  # 10 second timeout
                        except Exception as e:
                            logger.error(f"User simulation task failed: {e}")
                    
                    # Report statistics periodically
                    if datetime.now() - last_stats_report > stats_interval:
                        self._report_statistics()
                        last_stats_report = datetime.now()
                    
                    # Random sleep between simulation rounds
                    sleep_time = random.uniform(
                        self.config['simulation']['min_visit_interval'],
                        self.config['simulation']['max_visit_interval']
                    )
                    time.sleep(sleep_time)
                    
                except KeyboardInterrupt:
                    logger.info("Simulation interrupted by user")
                    break
                except Exception as e:
                    logger.error(f"Unexpected error in simulation loop: {e}")
                    self.stats['errors'] += 1
                    time.sleep(1)  # Brief pause before continuing
        
        self.is_running = False
        logger.info("Simulation completed")
        self._report_final_statistics()
    
    def _report_statistics(self) -> None:
        """Report current statistics"""
        logger.info("=== SIMULATION STATISTICS ===")
        logger.info(f"Total visits: {self.stats['total_visits']}")
        logger.info(f"Allowed visits: {self.stats['allowed_visits']}")
        logger.info(f"Blocked visits: {self.stats['blocked_visits']}")
        logger.info(f"Errors: {self.stats['errors']}")
        
        if self.stats['total_visits'] > 0:
            block_rate = (self.stats['blocked_visits'] / self.stats['total_visits']) * 100
            logger.info(f"Block rate: {block_rate:.2f}%")
        
        logger.info("============================")
    
    def _report_final_statistics(self) -> None:
        """Report final statistics and save to file"""
        self._report_statistics()
        
        # Save statistics to file
        stats_file = f"simulation_stats_{datetime.now().strftime('%Y%m%d_%H%M%S')}.json"
        try:
            with open(stats_file, 'w') as f:
                json.dump({
                    **self.stats,
                    'simulation_completed': datetime.now().isoformat(),
                    'config': self.config
                }, f, indent=4)
            logger.info(f"Final statistics saved to {stats_file}")
        except Exception as e:
            logger.error(f"Failed to save statistics: {e}")
    
    def stop(self) -> None:
        """Stop the simulation gracefully"""
        self.is_running = False
        logger.info("Stopping simulation...")
    
    def run_single_batch(self, num_visits: int = 50) -> None:
        """
        Run a single batch of simulated visits (useful for testing)
        
        Args:
            num_visits: Number of visits to simulate
        """
        logger.info(f"Starting single batch simulation with {num_visits} visits")
        
        # Enable simulation for this batch
        self.is_running = True
        
        # Initialize blocked sites cache
        self.website_generator.update_blocked_sites_cache(self.db_manager)
        
        for _ in range(num_visits):
            user_id = random.choice(list(self.user_simulators.keys()))
            self.simulate_user_session(user_id)
            
            # Small delay between visits
            time.sleep(random.uniform(0.1, 1.0))
        
        # Stop simulation
        self.is_running = False
        
        logger.info("Single batch simulation completed")
        self._report_statistics()
