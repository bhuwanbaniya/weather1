<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weather App</title>
    <!-- Fonts from Google -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Architects+Daughter&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./Bhuwan_baniya_2414002.css">
    <!-- Boxicons -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>

<body>
    <a href="index.php">Go to view past data</a>

    <form method="GET">
        <div class="card">
            <div class="search">
                <i class='bx bxs-map'></i>
                <input type="text" class="search-bar" placeholder="Search" name="t">
                <button class='bx bx-search' type="submit"></button>
            </div>
        </div>
    </form>

    <?php
    $servername = "localhost";
    $username = "root";
    $password = "";
    $database = "weatherapp2_db";

    // Create connection
    $conn = new mysqli($servername, $username, $password, $database);

    // Check database connection
    if ($conn->connect_error) {
        die("Database connection failed: " . $conn->connect_error);
    }

    // Handle form submission
    if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["t"])) {
        $weather = isset($_GET["t"]) ? $_GET["t"] : '';

        $apikey = '049a06099f75635728ff7415f72d35bc'; // Replace with your OpenWeatherMap API key
        $url = "https://api.openweathermap.org/data/2.5/weather?q={$weather}&appid={$apikey}&units=metric";

        // Fetch data from OpenWeatherMap API
        $response = file_get_contents($url);
        if ($response === false) {
            // Log the error to a file
            $errorMessage = error_get_last()['message'] ?? 'Unknown error';
            error_log("Error fetching data from OpenWeatherMap API: $errorMessage", 3, 'error.log');

            // Display a more detailed error message for debugging
            die("Error fetching data. Detailed error: $errorMessage");
        } else {
            $data = json_decode($response, true);

            if ($data == null) {
                echo "Failed to fetch data";
            } else {
                $city = $data['name'];
                $description = $data['weather'][0]['description'];
                $temperature = $data['main']['temp'];
                $humidity = $data['main']['humidity'];
                $weatherIcon = "https://openweathermap.org/img/wn/" . $data['weather'][0]['icon'] . "@2x.png";

                // Generate the current date and time in the format YYYY-MM-DD HH:MM:SS
                $datetime = date("Y-m-d H:i:s");

                // Create a prepared statement
                $stmt = $conn->prepare("INSERT INTO weather (name, description, temperature, humidity, date_time, weather_icon) VALUES (?, ?, ?, ?, ?, ?)");

                // Check if the statement was prepared successfully
                if (!$stmt) {
                    die("Error preparing statement: " . $conn->error);
                }

                // Bind parameters to the prepared statement
                $stmt->bind_param("ssddss", $city, $description, $temperature, $humidity, $datetime, $weatherIcon);

                // Execute the prepared statement to insert the weather data into the database
                if (!$stmt->execute()) {
                    die("Error executing statement: " . $stmt->error);
                }

                // Close the prepared statement
                $stmt->close();

                // Retrieve the latest weather entry for display
                $result = $conn->query("SELECT * FROM weather WHERE name = '$city' ORDER BY date_time DESC LIMIT 1");

                if ($result && $result->num_rows > 0) {
                    $latestWeather = $result->fetch_assoc();
                    // Display the latest weather information
                    echo "Latest Weather Data: <br>";
                    echo "City: " . $latestWeather['name'] . "<br>";
                    echo "Description: " . $latestWeather['description'] . "<br>";
                    echo "Temperature: " . $latestWeather['temperature'] . "°C<br>";
                    echo "Humidity: " . $latestWeather['humidity'] . "%<br>";
                    echo "Date/Time: " . $latestWeather['date_time'] . "<br>";
                    echo "<img src='" . $latestWeather['weather_icon'] . "' alt='Weather Icon'>";
                }
            }
        }
    }

    // Close the database connection
    $conn->close();
    ?>

    <script src="script.js"></script>
    <script>
        // Store data in local storage
        localStorage.setItem('city', 'los angeles');
        localStorage.setItem('temperature', '25°C');
        localStorage.setItem('weather', 'Sunny');

        // Retrieve data from local storage
        const city = localStorage.getItem('city');
        const temperature = localStorage.getItem('temperature');
        const weather = localStorage.getItem('weather');

        // Display retrieved data
        console.log('City:', city);
        console.log('Temperature:', temperature);
        console.log('Weather:', weather);
    </script>
</body>

</html>