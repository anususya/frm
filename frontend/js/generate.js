const myButton = document.getElementById("generate");

// Add a click event listener to the button
myButton.addEventListener("click", function () {
    generateData();
});
function generateData()
{
    sendParams(document.getElementById("count").value.trim());
}

function sendParams(field)
{
    let url = "/generate"; // Replace "yourpage.php" with the target URL

    let data = {
        field: field,
    };

    fetch(url, {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify(data)
    })
        .then(response => response.json())  // Assuming the server returns JSON data
        .then(data => {
            validateResult(data)
        })
            // You can handle the server's response here

        .catch(error => {
            console.error("Error:", error);
            alert("An error occurred.");
        });
}

function validateResult(result)
{
    const failText = document.getElementById("generate_fail");
    const successText = document.getElementById("generate_success");
    const generateButton = document.getElementById("parse_button");

    if (result.generate === false) {
        failText.classList.remove("d-none");
        successText.classList.add("d-none");
        generateButton.classList.add("d-none");
    } else {
        failText.classList.add("d-none");
        successText.classList.remove("d-none");
        generateButton.classList.remove("d-none");
    }
}