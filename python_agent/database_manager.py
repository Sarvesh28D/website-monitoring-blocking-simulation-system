#!/usr/bin/env python3
"""
Database Manager Module
======================

Manages database connections and operations with connection pooling
for the Website Monitoring & Blocking Simulation System.

Author: Expert Software Engineer
Created: September 10, 2025
Version: 1.0
"""

import mysql.connector
from mysql.connector import Error
import logging
from typing import Dict, Tuple

# Configure logging
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
