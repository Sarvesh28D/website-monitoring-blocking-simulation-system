#!/usr/bin/env python3
"""
User Simulator Module
====================

Simulates user browsing behavior with realistic patterns
for the Website Monitoring & Blocking Simulation System.

Author: Expert Software Engineer
Created: September 10, 2025
Version: 1.0
"""

import random
from typing import Dict, TYPE_CHECKING

# Import type hints only during type checking to avoid circular imports
if TYPE_CHECKING:
    from website_generator import WebsiteGenerator

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
        
        # Import here to avoid circular dependency
        from website_generator import WebsiteGenerator
        
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
        
    def generate_visit(self, website_generator: 'WebsiteGenerator') -> Dict[str, str]:
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
