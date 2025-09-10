#!/usr/bin/env python3
"""
Website Generator Module
=======================

Generates realistic website names and manages blocked sites list
for the Website Monitoring & Blocking Simulation System.

Author: Expert Software Engineer
Created: September 10, 2025
Version: 1.0
"""

import random
import logging
from datetime import datetime, timedelta
from typing import TYPE_CHECKING

# Import type hint only during type checking to avoid circular imports
if TYPE_CHECKING:
    from database_manager import DatabaseManager

# Configure logging
logger = logging.getLogger(__name__)

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
    
    def update_blocked_sites_cache(self, db_manager: 'DatabaseManager') -> None:
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
    
    def is_site_blocked(self, site_name: str, db_manager: 'DatabaseManager') -> bool:
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
