-- Create turfs table
CREATE TABLE IF NOT EXISTS `turfs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `turf_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `price_per_hour` decimal(10,2) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample turf data
INSERT INTO `turfs` (`turf_name`, `description`, `image_url`, `price_per_hour`, `status`) VALUES
('Premium Soccer Field', 'Professional grade turf with floodlights and premium facilities', 'img/turf1.jpg', 1500.00, 'active'),
('Standard Soccer Field', 'Well-maintained turf with basic amenities', 'img/turf2.jpg', 1000.00, 'active'),
('Training Ground', 'Perfect for practice sessions and amateur games', 'img/turf3.jpg', 800.00, 'active');
