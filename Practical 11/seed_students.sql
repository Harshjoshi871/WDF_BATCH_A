-- Practical 11: Seed Data for Students Table (25 Records)
-- CHARUSAT - ITUE203 Web Development Frameworks

INSERT INTO `students` (`id`, `name`, `email`, `course`, `year`, `phone`, `status`) VALUES
(1, 'Harsh Joshi', 'harsh@gmail.com', 'B.Tech IT', 2, '9876543210', 'active'),
(2, 'Aarav Patel', 'aarav.patel@studenthub.com', 'B.Tech CSE', 3, '9825123456', 'active'),
(3, 'Diya Shah', 'diya.shah@studenthub.com', 'B.Tech IT', 2, '9724123890', 'active'),
(4, 'Rohan Mehta', 'rohan.mehta@studenthub.com', 'BCA', 1, '9909234512', 'active'),
(5, 'Ananya Sharma', 'ananya.sharma@studenthub.com', 'B.Tech CSE', 4, '9426012345', 'active'),
(6, 'Kavya Desai', 'kavya.desai@studenthub.com', 'MCA', 1, '9879512345', 'active'),
(7, 'Siddharth Trivedi', 'siddharth.t@studenthub.com', 'B.Tech IT', 3, '9898123456', 'active'),
(8, 'Pooja Varma', 'pooja.varma@studenthub.com', 'BCA', 2, '9712345678', 'active'),
(9, 'Yash Parmar', 'yash.parmar@studenthub.com', 'B.Tech CSE', 2, '9601234567', 'active'),
(10, 'Tanvi Dave', 'tanvi.dave@studenthub.com', 'MCA', 2, '9978123456', 'active'),
(11, 'Devang Solanki', 'devang.s@studenthub.com', 'B.Tech IT', 4, '9824098765', 'active'),
(12, 'Isha Panchal', 'isha.panchal@studenthub.com', 'BCA', 3, '9428765432', 'active'),
(13, 'Manan Bhatt', 'manan.bhatt@studenthub.com', 'B.Tech CSE', 1, '9879123890', 'active'),
(14, 'Khushi Prajapati', 'khushi.p@studenthub.com', 'B.Tech IT', 2, '9904567890', 'active'),
(15, 'Dhruv Chaudhari', 'dhruv.c@studenthub.com', 'MCA', 1, '9725345678', 'active'),
(16, 'Riddhi Raval', 'riddhi.raval@studenthub.com', 'BCA', 1, '9898765432', 'active'),
(17, 'Parth Shukla', 'parth.shukla@studenthub.com', 'B.Tech CSE', 3, '9825678901', 'active'),
(18, 'Nidhi Kothari', 'nidhi.kothari@studenthub.com', 'B.Tech IT', 3, '9714567890', 'active'),
(19, 'Jayesh Barot', 'jayesh.barot@studenthub.com', 'MCA', 2, '9638123456', 'inactive'),
(20, 'Bhavin Rana', 'bhavin.rana@studenthub.com', 'B.Tech CSE', 2, '9979012345', 'active'),
(21, 'Shreya Jani', 'shreya.jani@studenthub.com', 'BCA', 2, '9429123456', 'active'),
(22, 'Meet Soni', 'meet.soni@studenthub.com', 'B.Tech IT', 1, '9876123450', 'active'),
(23, 'Prachi Chauhan', 'prachi.c@studenthub.com', 'B.Tech CSE', 4, '9824567123', 'active'),
(24, 'Akash Zala', 'akash.zala@studenthub.com', 'MCA', 1, '9909876543', 'active'),
(25, 'Kinjal Thakkar', 'kinjal.t@studenthub.com', 'BCA', 3, '9723456789', 'inactive')
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `course`=VALUES(`course`), `year`=VALUES(`year`), `phone`=VALUES(`phone`), `status`=VALUES(`status`);
