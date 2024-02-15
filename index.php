<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weather Forecast</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f0f0f0;
            color: #333;
        }

        .container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
            color: #444;
        }

        a {
            text-align: center;
            margin-bottom: 20px;
            color: #007bff;
            text-decoration: none;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        th,
        td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f2f2f2;
        }

        .day-header {
            background-color: #007bff;
            color: #fff;
            font-weight: bold;
            text-align: center;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 8px 8px 0 0;
        }

        img {
            max-width: 50px;
            display: block;
            margin: 0 auto;
        }
    </style>
</head>

<body>
    <div class="container">
        <a href="bhuwan.php">Go Back</a>
        <h1>Weather Forecast for the Next 7 Days</h1>

        <?php
        // Database connection
        $servername = "localhost";
        $username = "root";
        $password = "";
        $database = "weatherapp2_db";
        $conn = new mysqli($servername, $username, $password, $database);
        if ($conn->connect_error) {
            die("Database connection failed: " . $conn->connect_error);
        }

        // Get the current date
        $currentDate = date("Y-m-d");

        // Loop through the next 7 days
        for ($i = 0; $i < 7; $i++) {
            $nextDay = date("Y-m-d", strtotime("+$i day", strtotime($currentDate)));
            $dayOfWeek = date("l", strtotime($nextDay));

            // Query to fetch weather data for the current day
            $sql = "SELECT * FROM weather WHERE DATE(date_time) = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $nextDay);
            $stmt->execute();
            $result = $stmt->get_result();

            echo "<h2 class='day-header'>{$dayOfWeek}, {$nextDay}</h2>";

            if ($result->num_rows > 0) {
                echo "<table>";
                echo "<tr>
                        <th>Place</th>
                        <th>Description</th>
                        <th>Temperature (°C)</th>
                        <th>Humidity (%)</th>
                        <th>Date/Time</th>
                        <th>Weather Icon</th>
                    </tr>";

                // Output data of each row
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row["name"] . "</td>";
                    echo "<td>" . $row["description"] . "</td>";
                    echo "<td>" . $row["temperature"] . "</td>";
                    echo "<td>" . $row["humidity"] . "</td>";
                    echo "<td>" . $row["date_time"] . "</td>";
                    // Check if weather icon URL is available
                    if (!empty($row["weather_icon"])) {
                        echo "<td><img src=\"{$row["weather_icon"]}\" alt=\"weather icon\"></td>";
                    } else {
                        echo "<td>No icon available</td>";
                    }
                    echo "</tr>";
                }

                echo "</table>";
            } else {
                echo "<p>No weather data available for {$dayOfWeek}, {$nextDay}.</p>";
            }
        }
        $stmt->close();
        $conn->close();
        ?>
    </div>
</body>

</html>