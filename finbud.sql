-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 12, 2024 at 06:03 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `finbud`
--

-- --------------------------------------------------------

--
-- Table structure for table `budgets`
--

CREATE TABLE `budgets` (
  `budget_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `budgets`
--

INSERT INTO `budgets` (`budget_id`, `user_id`, `category_id`, `amount`, `start_date`, `end_date`) VALUES
(22, 1, 1, 13123123.00, '2024-11-11', '2024-11-30');

--
-- Triggers `budgets`
--
DELIMITER $$
CREATE TRIGGER `update_remaining_budget_after_budget_insert` AFTER INSERT ON `budgets` FOR EACH ROW BEGIN
    -- Khai báo biến để lưu tổng chi tiêu
    DECLARE total_expenses DECIMAL(10,2);

    -- Tính tổng chi tiêu cho category_id tương ứng
    SELECT COALESCE(SUM(amount), 0) INTO total_expenses
    FROM Expenses_transaction
    WHERE category_id = NEW.category_id;

    -- Thêm bản ghi mới vào remaining_budget với remaining_budget = amount - total_expenses
    INSERT INTO remaining_budget (budget_id, remaining_budget)
    VALUES (NEW.budget_id, NEW.amount - total_expenses);
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `update_remaining_budget_after_budget_update` AFTER UPDATE ON `budgets` FOR EACH ROW BEGIN
    DECLARE total_expenses DECIMAL(10,2);

    -- Tính tổng chi tiêu cho ngân sách có category_id liên quan
    SELECT COALESCE(SUM(amount), 0) INTO total_expenses
    FROM Expenses_transaction
    WHERE category_id = NEW.category_id;

    -- Cập nhật remaining_budget dựa trên ngân sách mới và tổng chi tiêu
    UPDATE remaining_budget rb
    SET rb.remaining_budget = NEW.amount - total_expenses
    WHERE rb.budget_id = NEW.budget_id;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`) VALUES
(1, 'Groceries'),
(2, 'Utilities'),
(3, 'Rent'),
(4, 'Transportation'),
(5, 'Dining Out'),
(6, 'Entertainment'),
(7, 'Healthcare'),
(8, 'Insurance'),
(9, 'Clothing'),
(10, 'Education'),
(11, 'Gifts'),
(12, 'Donations'),
(13, 'Savings'),
(14, 'Investments'),
(15, 'Household Supplies'),
(16, 'Personal Care'),
(17, 'Travel'),
(18, 'Childcare'),
(19, 'Subscriptions'),
(20, 'Miscellaneous');

-- --------------------------------------------------------

--
-- Table structure for table `category_reportfinance`
--

CREATE TABLE `category_reportfinance` (
  `report_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `total_expenses` decimal(10,2) DEFAULT NULL,
  `total_income` decimal(10,2) DEFAULT NULL,
  `remaining_budget` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `expenses_transaction`
--

CREATE TABLE `expenses_transaction` (
  `expense_transaction_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `expense_date` date NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `sub_category_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `expenses_transaction`
--

INSERT INTO `expenses_transaction` (`expense_transaction_id`, `user_id`, `category_id`, `amount`, `expense_date`, `description`, `sub_category_id`) VALUES
(53, 1, 12, 200.00, '2024-11-12', 'hettien', 51);

--
-- Triggers `expenses_transaction`
--
DELIMITER $$
CREATE TRIGGER `update_remaining_budget_after_expense` AFTER INSERT ON `expenses_transaction` FOR EACH ROW BEGIN
    DECLARE total_expenses DECIMAL(10,2);

    -- Tính tổng chi tiêu cho budget liên quan đến category_id mới
    SELECT COALESCE(SUM(amount), 0) INTO total_expenses
    FROM Expenses_transaction
    WHERE category_id = NEW.category_id;

    -- Cập nhật remaining_budget cho ngân sách tương ứng
    UPDATE remaining_budget rb
    JOIN Budgets b ON rb.budget_id = b.budget_id
    SET rb.remaining_budget = b.amount - total_expenses
    WHERE b.category_id = NEW.category_id;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `update_remaining_budget_after_expense_delete` AFTER DELETE ON `expenses_transaction` FOR EACH ROW BEGIN
    DECLARE total_expenses DECIMAL(10,2);

    -- Tính tổng chi tiêu cho ngân sách liên quan đến category_id đã bị xóa
    SELECT COALESCE(SUM(amount), 0) INTO total_expenses
    FROM Expenses_transaction
    WHERE category_id = OLD.category_id;

    -- Cập nhật remaining_budget cho ngân sách tương ứng
    UPDATE remaining_budget rb
    JOIN Budgets b ON rb.budget_id = b.budget_id
    SET rb.remaining_budget = b.amount - total_expenses
    WHERE b.category_id = OLD.category_id;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `update_remaining_budget_after_expense_update` AFTER UPDATE ON `expenses_transaction` FOR EACH ROW BEGIN
    DECLARE total_expenses DECIMAL(10,2);

    -- Tính tổng chi tiêu cho ngân sách liên quan đến category_id đã được cập nhật
    SELECT COALESCE(SUM(amount), 0) INTO total_expenses
    FROM Expenses_transaction
    WHERE category_id = NEW.category_id;

    -- Cập nhật remaining_budget cho ngân sách tương ứng
    UPDATE remaining_budget rb
    JOIN Budgets b ON rb.budget_id = b.budget_id
    SET rb.remaining_budget = b.amount - total_expenses
    WHERE b.category_id = NEW.category_id;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `update_reportfinance_after_expense_insert` AFTER INSERT ON `expenses_transaction` FOR EACH ROW BEGIN
    DECLARE total_expenses DECIMAL(10, 2);
    
    -- Calculate the total expenses for the user
    SELECT COALESCE(SUM(amount), 0) INTO total_expenses
    FROM expenses_transaction
    WHERE user_id = NEW.user_id;

    -- Update the existing row in ReportFinance with the calculated total_expenses
    UPDATE ReportFinance
    SET total_expenses = total_expenses,
        remaining_budget = total_income - total_expenses
    WHERE user_id = NEW.user_id;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `update_reportfinance_after_expense_update` AFTER INSERT ON `expenses_transaction` FOR EACH ROW BEGIN
    DECLARE total_expenses DECIMAL(10,2);
    DECLARE total_income DECIMAL(10,2);
    DECLARE remaining_budget DECIMAL(10,2);

    -- Calculate total expenses for the user
    SELECT COALESCE(SUM(amount), 0) INTO total_expenses
    FROM expenses_transaction
    WHERE user_id = NEW.user_id;

    -- Calculate total income for the user
    SELECT COALESCE(SUM(amount), 0) INTO total_income
    FROM income
    WHERE user_id = NEW.user_id;

    -- Calculate remaining budget
    SET remaining_budget = total_income - total_expenses;

    -- Delete any existing duplicate entries for the same user_id in ReportFinance
    DELETE FROM ReportFinance WHERE user_id = NEW.user_id;

    -- Insert a new record with the updated totals
    INSERT INTO ReportFinance (user_id, total_expenses, total_income, remaining_budget)
    VALUES (NEW.user_id, total_expenses, total_income, remaining_budget);
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `update_total_expenses_after_insert` AFTER INSERT ON `expenses_transaction` FOR EACH ROW BEGIN
    DECLARE total DECIMAL(10,2);

    -- Tính tổng chi tiêu cho user_id từ bảng Expenses_transaction
    SELECT COALESCE(SUM(amount), 0) INTO total
    FROM Expenses_transaction
    WHERE user_id = NEW.user_id;

    -- Cập nhật total_expenses trong bảng ReportFinance
    INSERT INTO ReportFinance (user_id, total_income, total_expenses, remaining_budget)
    VALUES (NEW.user_id, 0, total, -total) 
    ON DUPLICATE KEY UPDATE total_expenses = total, remaining_budget = total_income - total;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `update_total_expenses_after_update` AFTER UPDATE ON `expenses_transaction` FOR EACH ROW BEGIN
    DECLARE total DECIMAL(10,2);

    -- Tính tổng chi tiêu cho user_id từ bảng Expenses_transaction
    SELECT COALESCE(SUM(amount), 0) INTO total
    FROM Expenses_transaction
    WHERE user_id = NEW.user_id;

    -- Cập nhật chỉ total_expenses trong bảng ReportFinance
    UPDATE ReportFinance
    SET total_expenses = total
    WHERE user_id = NEW.user_id;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `financialgoals`
--

CREATE TABLE `financialgoals` (
  `goal_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `goal_name` varchar(100) NOT NULL,
  `target_amount` decimal(10,2) NOT NULL,
  `current_amount` decimal(10,2) DEFAULT 0.00,
  `target_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `financialgoals`
--

INSERT INTO `financialgoals` (`goal_id`, `user_id`, `goal_name`, `target_amount`, `current_amount`, `target_date`) VALUES
(1, 1, 'buy new car', 300000.00, 6324.00, '2024-11-30');

-- --------------------------------------------------------

--
-- Table structure for table `income`
--

CREATE TABLE `income` (
  `income_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `income_category_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `income_date` date NOT NULL,
  `description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `income`
--

INSERT INTO `income` (`income_id`, `user_id`, `income_category_id`, `amount`, `income_date`, `description`) VALUES
(10, 1, 1, 500.00, '2024-11-12', '7567');

--
-- Triggers `income`
--
DELIMITER $$
CREATE TRIGGER `update_total_income_after_insert` AFTER INSERT ON `income` FOR EACH ROW BEGIN
    DECLARE total DECIMAL(10, 2);

    -- Tính tổng thu nhập của user_id từ bảng income
    SELECT COALESCE(SUM(amount), 0) INTO total
    FROM income
    WHERE user_id = NEW.user_id;

    -- Kiểm tra nếu user_id đã tồn tại trong total_income
    IF EXISTS (SELECT 1 FROM total_income WHERE user_id = NEW.user_id) THEN
        -- Nếu tồn tại, cập nhật total_income
        UPDATE total_income
        SET total_income = total
        WHERE user_id = NEW.user_id;
    ELSE
        -- Nếu chưa tồn tại, thêm bản ghi mới
        INSERT INTO total_income (user_id, total_income) 
        VALUES (NEW.user_id, total);
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `update_total_income_after_insert_rp` AFTER INSERT ON `income` FOR EACH ROW BEGIN
    DECLARE total DECIMAL(10,2);

    -- Tính tổng thu nhập của user_id từ bảng Income
    SELECT COALESCE(SUM(amount), 0) INTO total
    FROM income
    WHERE user_id = NEW.user_id;

    -- Cập nhật total_income trong bảng ReportFinance
    UPDATE ReportFinance
    SET total_income = total
    WHERE user_id = NEW.user_id;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `income_category`
--

CREATE TABLE `income_category` (
  `income_category_id` int(11) NOT NULL,
  `category_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `income_category`
--

INSERT INTO `income_category` (`income_category_id`, `category_name`) VALUES
(1, 'Salary'),
(2, 'Freelance'),
(3, 'Investments'),
(4, 'Rental Income'),
(5, 'Gifts'),
(6, 'Business Income'),
(7, 'Savings Interest'),
(8, 'Stock Dividends'),
(9, 'Bonus'),
(10, 'Other');

-- --------------------------------------------------------

--
-- Table structure for table `remaining_budget`
--

CREATE TABLE `remaining_budget` (
  `remaining_budget_id` int(11) NOT NULL,
  `budget_id` int(11) DEFAULT NULL,
  `remaining_budget` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `remaining_budget`
--

INSERT INTO `remaining_budget` (`remaining_budget_id`, `budget_id`, `remaining_budget`) VALUES
(22, 22, 13123123.00);

-- --------------------------------------------------------

--
-- Table structure for table `reportfinance`
--

CREATE TABLE `reportfinance` (
  `report_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `total_expenses` decimal(10,2) DEFAULT 0.00,
  `total_income` decimal(10,2) DEFAULT 0.00,
  `remaining_budget` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reportfinance`
--

INSERT INTO `reportfinance` (`report_id`, `user_id`, `total_expenses`, `total_income`, `remaining_budget`) VALUES
(104, 1, 200.00, 500.00, 400.00);

-- --------------------------------------------------------

--
-- Table structure for table `saving_transaction`
--

CREATE TABLE `saving_transaction` (
  `saving_transaction_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `goal_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `transaction_date` date NOT NULL,
  `description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sub_category`
--

CREATE TABLE `sub_category` (
  `sub_category_id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `sub_category_name` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sub_category`
--

INSERT INTO `sub_category` (`sub_category_id`, `category_id`, `sub_category_name`) VALUES
(1, 1, 'Fruits & Vegetables'),
(2, 1, 'Meat & Seafood'),
(3, 1, 'Dairy Products'),
(4, 1, 'Bakery & Bread'),
(5, 1, 'Beverages'),
(6, 2, 'Electricity'),
(7, 2, 'Water'),
(8, 2, 'Gas'),
(9, 2, 'Internet'),
(10, 2, 'Trash Collection'),
(11, 3, 'Monthly Rent'),
(12, 3, 'Security Deposit'),
(13, 3, 'Maintenance Fees'),
(14, 4, 'Public Transport'),
(15, 4, 'Fuel'),
(16, 4, 'Taxi & Ride Sharing'),
(17, 4, 'Vehicle Maintenance'),
(18, 4, 'Parking Fees'),
(19, 5, 'Restaurants'),
(20, 5, 'Fast Food'),
(21, 5, 'Cafes & Coffee Shops'),
(22, 5, 'Takeout & Delivery'),
(23, 6, 'Movies & Theaters'),
(24, 6, 'Concerts'),
(25, 6, 'Sports Events'),
(26, 6, 'Streaming Services'),
(27, 6, 'Gaming'),
(28, 7, 'Doctor Visits'),
(29, 7, 'Medication'),
(30, 7, 'Dental Care'),
(31, 7, 'Vision Care'),
(32, 7, 'Therapy'),
(33, 8, 'Health Insurance'),
(34, 8, 'Car Insurance'),
(35, 8, 'Home Insurance'),
(36, 8, 'Life Insurance'),
(37, 9, 'Casual Wear'),
(38, 9, 'Workwear'),
(39, 9, 'Footwear'),
(40, 9, 'Accessories'),
(41, 9, 'Sportswear'),
(42, 10, 'Tuition'),
(43, 10, 'Books & Supplies'),
(44, 10, 'Online Courses'),
(45, 10, 'Workshops'),
(46, 10, 'School Fees'),
(47, 11, 'Birthday Gifts'),
(48, 11, 'Holiday Gifts'),
(49, 11, 'Wedding Gifts'),
(50, 11, 'Special Occasions'),
(51, 12, 'Charity'),
(52, 12, 'Fundraisers'),
(53, 12, 'Church Donations'),
(54, 12, 'Non-Profits'),
(55, 13, 'Emergency Fund'),
(56, 13, 'Retirement Fund'),
(57, 13, 'Investments'),
(58, 13, 'Education Fund'),
(59, 14, 'Stocks'),
(60, 14, 'Bonds'),
(61, 14, 'Mutual Funds'),
(62, 14, 'Real Estate'),
(63, 15, 'Cleaning Supplies'),
(64, 15, 'Laundry Supplies'),
(65, 15, 'Kitchen Essentials'),
(66, 15, 'Bathroom Supplies'),
(67, 16, 'Skincare'),
(68, 16, 'Haircare'),
(69, 16, 'Cosmetics'),
(70, 16, 'Toiletries'),
(71, 17, 'Flights'),
(72, 17, 'Accommodation'),
(73, 17, 'Transportation'),
(74, 17, 'Activities & Tours'),
(75, 18, 'Babysitting'),
(76, 18, 'Daycare'),
(77, 18, 'School Supplies'),
(78, 18, 'Clothing'),
(79, 19, 'Streaming Services'),
(80, 19, 'Gym Membership'),
(81, 19, 'Software Subscriptions'),
(82, 19, 'Magazine Subscriptions'),
(83, 20, 'Pet Care'),
(84, 20, 'Hobbies'),
(85, 20, 'Home Decor'),
(86, 20, 'Uncategorized');

-- --------------------------------------------------------

--
-- Table structure for table `total_income`
--

CREATE TABLE `total_income` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_income` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `total_income`
--

INSERT INTO `total_income` (`id`, `user_id`, `total_income`) VALUES
(1, 1, 500.00);

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_id`, `username`, `email`, `password`) VALUES
(1, 'trmanhson', '104191018@student.swin.edu.au', '1');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `budgets`
--
ALTER TABLE `budgets`
  ADD PRIMARY KEY (`budget_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `category_reportfinance`
--
ALTER TABLE `category_reportfinance`
  ADD PRIMARY KEY (`report_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `expenses_transaction`
--
ALTER TABLE `expenses_transaction`
  ADD PRIMARY KEY (`expense_transaction_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `fk_sub_category` (`sub_category_id`);

--
-- Indexes for table `financialgoals`
--
ALTER TABLE `financialgoals`
  ADD PRIMARY KEY (`goal_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `income`
--
ALTER TABLE `income`
  ADD PRIMARY KEY (`income_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `income_category_id` (`income_category_id`);

--
-- Indexes for table `income_category`
--
ALTER TABLE `income_category`
  ADD PRIMARY KEY (`income_category_id`);

--
-- Indexes for table `remaining_budget`
--
ALTER TABLE `remaining_budget`
  ADD PRIMARY KEY (`remaining_budget_id`),
  ADD KEY `budget_id` (`budget_id`);

--
-- Indexes for table `reportfinance`
--
ALTER TABLE `reportfinance`
  ADD PRIMARY KEY (`report_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `saving_transaction`
--
ALTER TABLE `saving_transaction`
  ADD PRIMARY KEY (`saving_transaction_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `goal_id` (`goal_id`);

--
-- Indexes for table `sub_category`
--
ALTER TABLE `sub_category`
  ADD PRIMARY KEY (`sub_category_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `total_income`
--
ALTER TABLE `total_income`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `budgets`
--
ALTER TABLE `budgets`
  MODIFY `budget_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `category_reportfinance`
--
ALTER TABLE `category_reportfinance`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `expenses_transaction`
--
ALTER TABLE `expenses_transaction`
  MODIFY `expense_transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `financialgoals`
--
ALTER TABLE `financialgoals`
  MODIFY `goal_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `income`
--
ALTER TABLE `income`
  MODIFY `income_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `income_category`
--
ALTER TABLE `income_category`
  MODIFY `income_category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `remaining_budget`
--
ALTER TABLE `remaining_budget`
  MODIFY `remaining_budget_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `reportfinance`
--
ALTER TABLE `reportfinance`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=105;

--
-- AUTO_INCREMENT for table `saving_transaction`
--
ALTER TABLE `saving_transaction`
  MODIFY `saving_transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `sub_category`
--
ALTER TABLE `sub_category`
  MODIFY `sub_category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=87;

--
-- AUTO_INCREMENT for table `total_income`
--
ALTER TABLE `total_income`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `budgets`
--
ALTER TABLE `budgets`
  ADD CONSTRAINT `budgets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `budgets_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`);

--
-- Constraints for table `category_reportfinance`
--
ALTER TABLE `category_reportfinance`
  ADD CONSTRAINT `category_reportfinance_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`),
  ADD CONSTRAINT `category_reportfinance_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`);

--
-- Constraints for table `expenses_transaction`
--
ALTER TABLE `expenses_transaction`
  ADD CONSTRAINT `expenses_transaction_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `expenses_transaction_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`),
  ADD CONSTRAINT `fk_sub_category` FOREIGN KEY (`sub_category_id`) REFERENCES `sub_category` (`sub_category_id`);

--
-- Constraints for table `financialgoals`
--
ALTER TABLE `financialgoals`
  ADD CONSTRAINT `financialgoals_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `income`
--
ALTER TABLE `income`
  ADD CONSTRAINT `income_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `income_ibfk_2` FOREIGN KEY (`income_category_id`) REFERENCES `income_category` (`income_category_id`);

--
-- Constraints for table `remaining_budget`
--
ALTER TABLE `remaining_budget`
  ADD CONSTRAINT `remaining_budget_ibfk_1` FOREIGN KEY (`budget_id`) REFERENCES `budgets` (`budget_id`) ON DELETE CASCADE;

--
-- Constraints for table `reportfinance`
--
ALTER TABLE `reportfinance`
  ADD CONSTRAINT `reportfinance_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `saving_transaction`
--
ALTER TABLE `saving_transaction`
  ADD CONSTRAINT `saving_transaction_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `saving_transaction_ibfk_2` FOREIGN KEY (`goal_id`) REFERENCES `financialgoals` (`goal_id`);

--
-- Constraints for table `sub_category`
--
ALTER TABLE `sub_category`
  ADD CONSTRAINT `sub_category_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`);

--
-- Constraints for table `total_income`
--
ALTER TABLE `total_income`
  ADD CONSTRAINT `total_income_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
