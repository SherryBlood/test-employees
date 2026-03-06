<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <link rel="icon" href="icons/pageicon.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Login</title>
</head>

<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <form id="loginForm" action="authorization.php" method="POST" class="bg-white p-8 rounded shadow-md w-96">
        <h2 class="text-2xl mb-6 font-bold">Login</h2>

        <input type="text" name="login" id="login" placeholder="Username"
            class="w-full p-2 mb-4 border rounded focus:outline-none focus:ring-2 focus:ring-blue-400">

        <input type="password" name="password" id="password" placeholder="Password"
            class="w-full p-2 mb-4 border rounded focus:outline-none focus:ring-2 focus:ring-blue-400">

        <button type="submit" class="w-full bg-blue-500 text-white p-2 rounded hover:bg-blue-600 transition duration-200">
            Login
        </button>

        <p id="errorText" class="text-red-500 mt-4 text-sm <?= isset($_GET['error']) ? 'visible' : 'invisible' ?>">
            <?= isset($_GET['error']) && $_GET['error'] == 'empty' ? 'Please fill in all fields' : 'Invalid username or password' ?>
        </p>
    </form>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const login = document.getElementById('login').value.trim();
            const pass = document.getElementById('password').value.trim();

            if (login === '' || pass === '') {
                e.preventDefault();

                Swal.fire({
                    title: 'Empty fields!',
                    text: 'Please enter your username and password.',
                    icon: 'warning',
                    confirmButtonColor: '#3b82f6',
                    heightAuto: false
                });
            }
        });
    </script>
</body>

</html>