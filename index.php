<?php
session_start();

$profile_icon = $_SESSION['profile_icon'] ?? null;
$username = $_SESSION['username'] ?? null;
$first_letter = $username ? strtoupper(substr($username, 0, 1)) : null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Question and Answer</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">

    <header class="bg-gray-700 text-white shadow-md p-4 flex flex-col md:flex-row md:justify-between items-center text-center">
        <h1 class="md:text-xl font-bold">QUESTION AND ANSWER</h1>
        <nav class="flex flex-col md:flex-row space-y-2 md:space-y-0 md:space-x-4">
            <a class="text-white" href="index.php">Home</a>
            <a class="text-white" href="#about">About Us</a>
            <a class="text-white" href="#contact">Contact Us</a>
        </nav>
        <?php if ($username): ?>
            <div class="relative">
                <button id="dropdown-button" onclick="toggleDropdown()" class="flex items-center bg-blue-500 text-white px-4 py-2 rounded-full">
                    <?php if ($profile_icon): ?>
                        <img src="<?php echo htmlspecialchars($profile_icon); ?>" alt="Profile" class="w-8 h-8 rounded-full mr-2">
                    <?php else: ?>
                        <span class="w-8 h-8 flex items-center justify-center bg-gray-300 text-gray-700 font-bold rounded-full mr-2">
                            <?php echo $first_letter; ?>
                        </span>
                    <?php endif; ?>
                    <span class="hidden sm:inline">Welcome, <?php echo htmlspecialchars($username); ?> ▼</span>
                </button>
                <div id="dropdown" class="hidden absolute right-0 mt-2 w-40 bg-white shadow-md rounded-lg">
                    <a href="settings.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Settings</a>
                    <a href="public/logout.php" class="block px-4 py-2 text-red-600 hover:bg-gray-100">Logout</a>
                </div>
            </div>
        <?php else: ?>
            <div class="flex gap-2">
                <a href="public/signup.php" class="bg-blue-500 text-white px-4 py-2 rounded">Sign Up</a>
                <a href="public/signin.php" class="bg-gray-500 text-white px-4 py-2 rounded">Sign In</a>
            </div>
        <?php endif; ?>
    </header>
    
    <div class="container mx-auto mt-4 flex flex-col  gap-4 flex-grow px-2">
        <div class="w-full flex flex-col md:flex-row justify-center items-center gap-4">
            <div class="w-full md:w-1/2 my-6 md:my-12 relative">
                <img src="./uploads/signin.jpg" alt="Sign In Image" class="h-full w-full object-cover rounded-md">
                <div class="bg-black opacity-70 absolute top-0 right-0 left-0 bottom-0 rounded-md flex items-center justify-center text-white">
                    <div class="flex space-y-2 md:space-x-4 flex-col md:flex-row">
                        <?php if (!$username): ?>
                            <a href="./public/signin.php" class="bg-black border border-white text-white px-4 py-2 rounded-full">Get Started</a>
                        <?php endif; ?>

                            <a href="qa.php" class="bg-black border border-red-500 text-red-500 px-4 py-2 rounded-full">Go to Forum</a>
                    </div>
                </div>
            </div>
            <div id="about" class="w-full md:w-1/2 p-4 rounded-lg shadow-xs p-4 h-max">
                <h2 class="flex justify-center items-center text-2xl font-semibold mb-2">About Us</h2>
                <p class="mb-2 text-md md:text-lg">
                    Welcome to our Question and Answer forum! This platform is designed for learners who want to share knowledge, 
                    ask questions, and engage in meaningful discussions. Whether you have a complex programming question or need help 
                    understanding a concept, this is the place for you.
                </p>
                <p class="mb-2 text-md md:text-lg">
                    Users can create an account and start participating in discussions right away. The platform encourages collaborative 
                    learning, allowing members to upvote valuable answers and contribute their expertise.
                </p>
                <p class="text-md md:text-lg">
                    We also support group discussions, so learners can form study groups and collaborate on topics of interest. Our 
                    mission is to create a community-driven space where knowledge flows freely, just like Stack Overflow but tailored 
                    for deeper engagement and interactive learning.
                </p>
            </div>
        </div>
<section id="contact" class="bg-white p-4 rounded-lg shadow-md flex flex-col justify-center items-center py-16">
        <h2 class="text-2xl font-semibold mb-2 flex justify-center">Contact Us</h2>

        <form class="mb-4 flex flex-col space-y-4 md:space-y-0 md:flex-row md:space-x-10 md:items-end">
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Subscribe to our Newsletter</label>
                <div class="flex mt-2 flex-col sm:flex-row sm:items-center sm:space-x-2">
                    <input type="email" id="email" name="email" placeholder="Enter your email"
                        class="px-4 py-2 border rounded-l-lg focus:outline-none focus:ring focus:ring-blue-400">
                    <button type="submit" id="subscribeButton" class="px-2 mt-2 sm:mt-0 bg-blue-600 text-white py-2 rounded-r-lg hover:bg-blue-700">
                        Subscribe
                    </button>
                </div>
            </div>
            <div class="">
                <p class="text-sm font-medium text-gray-700">Email:</p>
                <a href="mailto:qaforum@gmail.com" class="text-blue-600 hover:underline">qaforum@gmail.com</a>
            </div>
            <div class="">
                <p class="text-sm font-medium text-gray-700">Phone:</p>
                <p class="text-gray-800">+254 712 345 678</p>
            </div>
        </form>
    </section>
    <footer class="bg-gray-900 text-white text-center p-2 mt-auto">
        &copy; 2025 Question and Answer Forum
    </footer>

    <script src="js/main.js" defer>
        html {
            scroll-behavior: smooth;
        }
    </script>
</body>
</html>