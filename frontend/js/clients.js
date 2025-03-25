document.addEventListener('DOMContentLoaded', sendParams);

function sendParams()
{
    // Get values from the input fields
    let country = document.getElementById("country").value.trim();
    let city = document.getElementById("city").value.trim();
    let active = document.getElementById("active").value.trim();
    let gender = document.getElementById("gender").value.trim();
    let birth_date_from = document.getElementById("birth_date_from").value.trim();
    let birth_date_to = document.getElementById("birth_date_to").value.trim();

    // Start the base URL for GET request
    let url = "/clients/client/get?"; // Replace "yourpage.php" with the target URL

    // Add non-empty inputs as GET parameters
    let params = [];
    if (country) {
        params.push("country=" + encodeURIComponent(country)); // If name is not empty
    }
    if (city) {
        params.push("city=" + encodeURIComponent(city)); // If age is not empty
    }
    if (active) {
        params.push("is_active=" + encodeURIComponent(active)); // If city is not empty
    }
    if (gender) {
        params.push("gender=" + encodeURIComponent(gender));
    }
    if (birth_date_from) {
        params.push("birth_date_from=" + encodeURIComponent(birth_date_from));
    }
    if (birth_date_to) {
        params.push("birth_date_to=" + encodeURIComponent(birth_date_to));
    }

    // If there are parameters, join them with '&' and append to the URL
    if (params.length > 0) {
        url += params.join("&");
    }

    // Redirect to the URL with the GET parameters
    // window.location.href = url;

    fetch(url, {
        method: "GET",
        headers: {
            "Content-Type": "application/json"
        }
    })
        .then(response => response.json())  // Assuming the server returns JSON data
        .then(data => {
            $('#table').bootstrapTable({data: data});
            $('#table').bootstrapTable('load', data);

            console.log("Response Data:", data);
            // You can handle the server's response here
        })
        .catch(error => {
            console.error("Error:", error);
            alert("An error occurred.");
        });
}