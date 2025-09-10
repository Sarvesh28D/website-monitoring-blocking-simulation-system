#!/usr/bin/env python3
"""
Website Monitoring & Blocking Simulation Agent
==============================================

This module simulates user browsing behavior by generating random website visits,
checking against a blocked sites list, and logging all activity to a MySQL database.

Author: Expert Software Engineer
Created: September 10, 2025
Version: 1.0
"""

import mysql.connector
from mysql.connector import Error
import random
import time
import logging
from datetime import datetime, timedelta
import json
import os
from typing import List, Dict, Optional, Tuple
import hashlib
import threading
from concurrent.futures import ThreadPoolExecutor
import signal
import sys

# Configure logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s',
    handlers=[
        logging.FileHandler('agent.log'),
        logging.StreamHandler(sys.stdout)
    ]
)
logger = logging.getLogger(__name__)

class DatabaseManager:
    """
    Manages database connections and operations with connection pooling
    """
    
    def __init__(self, config: Dict[str, str]):
        """
        Initialize database manager with configuration
        
        Args:
            config: Dictionary containing database connection parameters
        """
        self.config = config
        self.connection_pool = None
        self._initialize_connection_pool()
        
    def _initialize_connection_pool(self) -> None:
        """Initialize MySQL connection pool for better performance"""
        try:
            self.connection_pool = mysql.connector.pooling.MySQLConnectionPool(
                pool_name="website_monitoring_pool",
                pool_size=10,
                pool_reset_session=True,
                **self.config
            )
            logger.info("Database connection pool initialized successfully")
        except Error as e:
            logger.error(f"Failed to initialize connection pool: {e}")
            raise
    
    def get_connection(self):
        """Get a connection from the pool"""
        try:
            return self.connection_pool.get_connection()
        except Error as e:
            logger.error(f"Failed to get database connection: {e}")
            raise
    
    def execute_query(self, query: str, params: Tuple = None, fetch: bool = False):
        """
        Execute a database query with proper error handling
        
        Args:
            query: SQL query string
            params: Query parameters tuple
            fetch: Whether to fetch results
            
        Returns:
            Query results if fetch=True, otherwise None
        """
        connection = None
        cursor = None
        
        try:
            connection = self.get_connection()
            cursor = connection.cursor(dictionary=True)
            
            cursor.execute(query, params or ())
            
            if fetch:
                results = cursor.fetchall()
                return results
            else:
                connection.commit()
                return cursor.rowcount
                
        except Error as e:
            if connection:
                connection.rollback()
            logger.error(f"Database query failed: {e}")
            logger.error(f"Query: {query}")
            logger.error(f"Params: {params}")
            raise
            
        finally:
            if cursor:
                cursor.close()
            if connection:
                connection.close()

class WebsiteGenerator:
    """
    Generates realistic website names and manages blocked sites list
    """
    
    def __init__(self):
        """Initialize website generator with predefined lists"""
        
        # Common website categories
        self.website_categories = {
            'social': ['facebook.com', 'twitter.com', 'instagram.com', 'linkedin.com', 'snapchat.com'],
            'news': ['cnn.com', 'bbc.com', 'reuters.com', 'nytimes.com', 'washingtonpost.com'],
            'tech': ['github.com', 'stackoverflow.com', 'techcrunch.com', 'wired.com', 'arstechnica.com'],
            'entertainment': ['youtube.com', 'netflix.com', 'spotify.com', 'twitch.tv', 'hulu.com'],
            'ecommerce': ['amazon.com', 'ebay.com', 'walmart.com', 'target.com', 'bestbuy.com'],
            'education': ['coursera.org', 'edx.org', 'khanacademy.org', 'udemy.com', 'codecademy.com'],
            'productivity': ['google.com', 'microsoft.com', 'dropbox.com', 'slack.com', 'notion.so'],
            'finance': ['paypal.com', 'chase.com', 'bankofamerica.com', 'mint.com', 'robinhood.com']
        }
        
        # Flatten all websites
        self.all_websites = []
        for category_sites in self.website_categories.values():
            self.all_websites.extend(category_sites)
            
        # Add some additional random websites
        additional_sites = [
            'reddit.com', 'wikipedia.org', 'medium.com', 'quora.com', 'pinterest.com',
            'tumblr.com', 'flickr.com', 'vimeo.com', 'soundcloud.com', 'bandcamp.com',
            'goodreads.com', 'imdb.com', 'rottentomatoes.com', 'metacritic.com',
            'weather.com', 'accuweather.com', 'maps.google.com', 'yelp.com', 'tripadvisor.com'
        ]
        self.all_websites.extend(additional_sites)
        
        # Blocked sites cache
        self.blocked_sites_cache = set()
        self.cache_expiry = datetime.now()
        
    def get_random_website(self) -> str:
        """
        Get a random website from the predefined list
        
        Returns:
            Random website name
        """
        return random.choice(self.all_websites)
    
    def update_blocked_sites_cache(self, db_manager: DatabaseManager) -> None:
        """
        Update the blocked sites cache from database
        
        Args:
            db_manager: Database manager instance
        """
        try:
            query = "SELECT site_name FROM blocked_sites"
            results = db_manager.execute_query(query, fetch=True)
            
            self.blocked_sites_cache = {site['site_name'] for site in results}
            self.cache_expiry = datetime.now() + timedelta(minutes=5)  # Cache for 5 minutes
            
            logger.info(f"Updated blocked sites cache with {len(self.blocked_sites_cache)} sites")
            
        except Exception as e:
            logger.error(f"Failed to update blocked sites cache: {e}")
    
    def is_site_blocked(self, site_name: str, db_manager: DatabaseManager) -> bool:
        """
        Check if a site is blocked, with caching for performance
        
        Args:
            site_name: Website to check
            db_manager: Database manager instance
            
        Returns:
            True if site is blocked, False otherwise
        """
        # Refresh cache if expired
        if datetime.now() > self.cache_expiry:
            self.update_blocked_sites_cache(db_manager)
            
        return site_name in self.blocked_sites_cache

class UserSimulator:
    """
    Simulates user browsing behavior with realistic patterns
    """
    
    def __init__(self, user_id: int):
        """
        Initialize user simulator
        
        Args:
            user_id: Unique identifier for the user
        """
        self.user_id = user_id
        
        # User-specific browsing patterns
        self.browsing_frequency = random.uniform(0.5, 3.0)  # visits per minute
        self.session_duration = random.randint(30, 300)  # seconds per session
        self.favorite_categories = random.sample(list(WebsiteGenerator().website_categories.keys()), 
                                                random.randint(2, 4))
        
        # User agent strings for variety
        self.user_agents = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
            'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:91.0) Gecko/20100101 Firefox/91.0'
        ]
        
        # Simulated IP addresses
        self.ip_addresses = [
            f"192.168.1.{random.randint(100, 254)}",
            f"10.0.0.{random.randint(100, 254)}",
            f"172.16.0.{random.randint(100, 254)}"
        ]
        
    def generate_visit(self, website_generator: WebsiteGenerator) -> Dict[str, str]:
        """
        Generate a realistic website visit
        
        Args:
            website_generator: Website generator instance
            
        Returns:
            Dictionary containing visit details
        """
        # Prefer favorite categories 70% of the time
        if random.random() < 0.7 and self.favorite_categories:
            category = random.choice(self.favorite_categories)
            site = random.choice(website_generator.website_categories[category])
        else:
            site = website_generator.get_random_website()
            
        return {
            'site_name': site,
            'user_agent': random.choice(self.user_agents),
            'ip_address': random.choice(self.ip_addresses)
        }

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
                INSERT INTO sites_visited (user_id, site_name, status, user_agent, ip_address)
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
        
        # Initialize blocked sites cache
        self.website_generator.update_blocked_sites_cache(self.db_manager)
        
        for _ in range(num_visits):
            user_id = random.choice(list(self.user_simulators.keys()))
            self.simulate_user_session(user_id)
            
            # Small delay between visits
            time.sleep(random.uniform(0.1, 1.0))
        
        logger.info("Single batch simulation completed")
        self._report_statistics()

def main():
    """
    Main entry point for the monitoring agent
    """
    print("Website Monitoring & Blocking Simulation Agent")
    print("=" * 50)
    
    try:
        # Initialize the agent
        agent = MonitoringAgent()
        
        # Initialize user simulators
        agent._initialize_users()
        
        # Get user input for simulation mode
        print("\nSimulation Modes:")
        print("1. Continuous simulation (runs until stopped)")
        print("2. Single batch (50 visits)")
        print("3. Custom batch")
        
        choice = input("\nSelect mode (1-3) or press Enter for continuous: ").strip()
        
        if choice == '2':
            agent.run_single_batch(50)
        elif choice == '3':
            try:
                num_visits = int(input("Enter number of visits: "))
                agent.run_single_batch(num_visits)
            except ValueError:
                print("Invalid number, using default (50)")
                agent.run_single_batch(50)
        else:
            print("Starting continuous simulation... Press Ctrl+C to stop")
            agent.run_continuous_simulation()
            
    except KeyboardInterrupt:
        print("\nSimulation stopped by user")
    except Exception as e:
        logger.error(f"Fatal error: {e}")
        print(f"Error: {e}")
    finally:
        print("Agent shutdown complete")

if __name__ == "__main__":
    main()
