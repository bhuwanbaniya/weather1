const apiKey = "049a06099f75635728ff7415f72d35bc";

// Function to store city data in local storage
function storeCityData(cityName, temperature, weather) {
    const cityData = {
        temperature: temperature,
        weather: weather
    };
    localStorage.setItem(cityName.toLowerCase(), JSON.stringify(cityData));
}

// Function to retrieve city data from local storage
function getCityData(cityName) {
    const storedCityData = localStorage.getItem(cityName.toLowerCase());
    if (storedCityData) {
        const { temperature, weather } = JSON.parse(storedCityData);
        console.log('City:', cityName);
        console.log('Temperature:', temperature);
        console.log('Weather:', weather);
    } else {
        console.log('No data found for', cityName);
    }
}

async function fetchWeatherApi(city) {
    try {
        let weatherData = null;
        const localStorageData = localStorage.getItem(city.toLowerCase());
        if (localStorageData) {
            const { timestamp, data } = JSON.parse(localStorageData);
            // Check if data is fresh (less than 1 hour old)
            if (Date.now() - timestamp < 60 * 60 * 1000) {
                weatherData = data;
                displayWeatherFromLocalStorage(weatherData);
                if (!navigator.onLine) {
                    displayAlert("Weather data fetched from local storage", "#000000e0");
                }
            }
        }

        if ((!weatherData || !navigator.onLine) && navigator.onLine) {
            const weatherUrl = `https://api.openweathermap.org/data/2.5/weather?q=${city}&units=metric&appid=${apiKey}`;
            const weatherResponse = await fetch(weatherUrl);
            weatherData = await weatherResponse.json();

            if (weatherData.cod === "404") {
                displayAlert("City not found", "#000000e0");
            } else {
                getWeather(weatherData);
                // Save data to local storage with timestamp
                const temperature = weatherData.main.temp;
                const weather = weatherData.weather[0].main;
                storeCityData(city, temperature, weather); // Call storeCityData function here
            }
        } else if (!weatherData && !navigator.onLine) {
            displayAlert("You are offline and no data available in local storage", "#000000e0");
        }
        else if (!weatherData && !navigator.onLine) {
            displayAlert("You are offline and no data available", "#000000e0");
        }
    } catch (error) {
        displayAlert(`${error}`, " #000000e0");
    }
}

async function fetchWeather(city) {
    try {
        const units = 'units=metric';
        const response = await fetch(`https://api.openweathermap.org/data/2.5/weather?q=${city}&${units}&appid=${apiKey}`);
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Error fetching weather data:', error);
        throw error;
    }
}

function displayWeather(data) {
    const name = data.name;
    const icon = data.weather[0].icon;
    const description = data.weather[0].description;
    const humidity = data.main.humidity;
    const temp = data.main.temp;
    const speed = data.wind.speed;
    const form = document.querySelector("form");

    document.querySelector(".city").innerText = "Weather in " + name;
    document.querySelector(".icon").src = `https://openweathermap.org/img/wn/${icon}.png`;
    document.querySelector(".description").innerText = description;
    document.querySelector(".temp").innerText = temp + "°C";
    document.querySelector(".humidity").innerText = "Humidity: " + humidity + "%";
    document.querySelector(".wind").innerText = "Wind Speed: " + speed + " km/h";
    document.querySelector(".weather").classList.remove("loading");
}

async function search() {
    let city = document.querySelector(".search-bar").value;
    if (city) {
        try {
            const data = await fetchWeather(city);
            displayWeather(data);
            // Store the latest weather data for the searched city
            const temperature = data.main.temp;
            const weather = data.weather[0].main;
            storeCityData(city, temperature, weather); // Call storeCityData function here
        } catch (error) {
            console.error('Error handling weather data:', error);
        }
    } else {
        alert('Please enter a city name.');
    }
}

async function initialWeatherFetch() {
    try {
        const data = await fetchWeather("Los Angeles");
        displayWeather(data);
    } catch (error) {
        console.error('Error handling initial weather data:', error);
    }
}

document.querySelector(".search button")
    .addEventListener("click", search);

document.querySelector(".search-bar")
    .addEventListener("keyup", function (event) {
        if (event.key === "Enter") {
            search();
        }
    });

const form = document.querySelector("form");
form.addEventListener('submit', (event) => {
    event.preventDefault();
});

initialWeatherFetch();
