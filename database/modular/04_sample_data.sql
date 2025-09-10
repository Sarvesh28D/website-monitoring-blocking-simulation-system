-- Website Monitoring System - Sample Data
-- Created: September 10, 2025
-- Author: Expert Software Engineer
-- Purpose: Insert comprehensive sample data for testing and demonstration

USE website_monitoring;

-- Insert blocked sites with proper categorization
INSERT INTO blocked_sites (site_name, reason, category, severity, created_by) VALUES
-- Social Media Sites
('facebook.com', 'Social media blocked during work hours', 'social_media', 'medium', 'admin'),
('twitter.com', 'Social media - productivity policy', 'social_media', 'medium', 'admin'),  
('instagram.com', 'Social media - company policy', 'social_media', 'medium', 'admin'),
('linkedin.com', 'Professional networking - limited access', 'social_media', 'low', 'admin'),
('snapchat.com', 'Social media - mobile messaging app', 'social_media', 'medium', 'admin'),
('tiktok.com', 'Social media - short video platform', 'social_media', 'medium', 'admin'),

-- Streaming and Entertainment
('youtube.com', 'Video streaming - bandwidth conservation', 'streaming', 'medium', 'admin'),
('netflix.com', 'Streaming service - workplace policy', 'streaming', 'medium', 'admin'),
('hulu.com', 'Video streaming - productivity concerns', 'streaming', 'medium', 'admin'),
('disney-plus.com', 'Streaming service - entertainment', 'streaming', 'low', 'admin'),
('twitch.tv', 'Live streaming - gaming content', 'streaming', 'medium', 'admin'),

-- Gambling and Crypto
('gambling-site.com', 'Gambling content - company ethics policy', 'gambling', 'high', 'security'),
('crypto-gambling.com', 'Cryptocurrency gambling - financial policy', 'crypto', 'high', 'security'),
('poker-online.com', 'Online poker - gambling policy', 'gambling', 'high', 'security'),
('casino-games.com', 'Online casino - prohibited content', 'gambling', 'high', 'security'),

-- Adult and Inappropriate Content  
('adult-content.com', 'Adult content - inappropriate for workplace', 'adult_content', 'critical', 'security'),
('dating-site.com', 'Dating platforms - workplace appropriateness', 'dating', 'medium', 'admin'),

-- Security Threats
('malicious-site.com', 'Known malware distributor - security threat', 'malware', 'critical', 'security'),
('phishing-site.com', 'Phishing attempts - security policy', 'phishing', 'critical', 'security'),
('virus-download.com', 'Malware distribution site', 'malware', 'critical', 'security'),
('fake-bank.com', 'Phishing site targeting financial institutions', 'phishing', 'critical', 'security'),

-- P2P and File Sharing
('torrent-site.com', 'P2P file sharing - legal compliance', 'p2p', 'high', 'legal'),
('pirate-downloads.com', 'Copyright infringement - legal policy', 'p2p', 'high', 'legal'),

-- Gaming
('gaming-site.com', 'Online gaming - productivity policy', 'gaming', 'medium', 'admin'),
('steam.com', 'Gaming platform - work hour restrictions', 'gaming', 'low', 'admin'),

-- Dark Web and Anonymous
('darkweb-market.com', 'Dark web marketplace - security and legal concerns', 'darkweb', 'critical', 'security'),

-- Productivity Killers
('time-waster.com', 'Identified time-wasting site - productivity optimization', 'productivity', 'medium', 'admin'),
('meme-site.com', 'Entertainment content - productivity policy', 'productivity', 'low', 'admin'),
('gossip-news.com', 'Entertainment news - workplace focus', 'productivity', 'low', 'admin');

-- Insert sample user sessions
INSERT INTO user_sessions (user_id, session_id, ip_address, user_agent, total_visits, blocked_attempts, is_active) VALUES
(1, 'sess_001_user1_20250910', '192.168.1.101', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 25, 4, FALSE),
(2, 'sess_002_user2_20250910', '192.168.1.102', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36', 30, 2, FALSE),
(3, 'sess_003_user3_20250910', '192.168.1.103', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36', 28, 3, FALSE),
(4, 'sess_004_user4_20250910', '192.168.1.104', 'Mozilla/5.0 (Windows NT 11.0; Win64; x64) AppleWebKit/537.36', 22, 1, FALSE),
(5, 'sess_005_user5_20250910', '192.168.1.105', 'Mozilla/5.0 (iPad; CPU OS 14_7_1 like Mac OS X) AppleWebKit/605.1.15', 27, 5, FALSE);

-- Note: The sites_visited table will be populated by the Python simulation agent
-- This ensures realistic, time-distributed data rather than static bulk inserts
