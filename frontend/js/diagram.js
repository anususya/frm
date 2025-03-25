// document.addEventListener('DOMContentLoaded', sendParams);
document.addEventListener('DOMContentLoaded', selectOption);
const myChart = initializeChart({});
function selectOption()
{
    $('.dropdown-item').on('click', function (e) {
        e.preventDefault();
        sendParams($(this).data('name'));
    });
}


function sendParams(field)
{
    let url = "/clients/client/getCountByField"; // Replace "yourpage.php" with the target URL

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
            data = prepareDataForChart(data, field);
            updateChart(data);
            // You can handle the server's response here
        })
        .catch(error => {
            console.error("Error:", error);
            alert("An error occurred.");
        });
}

function getRandomColor()
{
    const letters = '0123456789ABCDEF';
    let color = '#';

    for (let i = 0; i < 6; i++) {
        color += letters[Math.floor(Math.random() * 16)];
    }

    return color;
}


function prepareDataForChart(data, field)
{
    let labels = [];
    let res = [];
    let colors = [];

    data.forEach(function (item) {
        labels.push(item[field]);
        res.push(item.count);
        colors.push(getRandomColor())
    });

    return {
        labels: labels, // Labels for the segments
        datasets: [{
            label: 'My Pie Chart',
            data: res, // Data corresponding to each label
            backgroundColor: colors, // Colors of each segment
            hoverOffset: 4 // Optional: adds a hover effect where the segment moves
        }]
    };
}

function initializeChart(data)
{
    const ctx = document.getElementById('myPieChart').getContext('2d');

    return new Chart(ctx, {
        type: 'doughnut', // Specifies the chart type (pie chart)
        data: data, // Pass the data
        options: {
            responsive: true, // Make the chart responsive
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top', // Position of the legend
                },
                tooltip: {
                    callbacks: {
                        // Tooltip content customization (optional)
                        label: function (tooltipItem) {
                            return tooltipItem.label + ': ' + tooltipItem.raw + ' units';
                        }
                    }
                }
            }
        }
    });
}

function updateChart(data)
{
    myChart.data = data;
    myChart.update();
}