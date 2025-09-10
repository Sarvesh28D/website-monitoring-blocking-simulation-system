#!/usr/bin/env python3
"""
Website Monitoring & Blocking Simulation Agent
==============================================

This module serves as the main entry point for the Website Monitoring & Blocking
Simulation System. It coordinates modular components to simulate user browsing
behavior and log activity to a MySQL database.

Author: Expert Software Engineer
Created: September 10, 2025
Version: 1.0
"""

import logging
import sys

# Import modular components
from monitoring_agent import MonitoringAgent

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
