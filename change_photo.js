// Change Photo
// Function to update the patterns with either environment or food images
// change_photo.js
function updatePatterns(buttonId, data, svg) {
    data.forEach((d, i) => {
        let imageUrl;
        if (buttonId === "environment") {
            imageUrl = d.r_photo_env1;
        } else if (buttonId === "food") {
            imageUrl = d.r_photo_food1;
        }
        svg.select(`#pattern-${i} image`).attr("xlink:href", imageUrl);
    });
}


// Add event listeners for both buttons

// document.getElementById("environment").addEventListener("click", function () {
//     updatePatterns("environment", data, svg); // Pass the ID of the clicked button
// });

// document.getElementById("food").addEventListener("click", function () {
//     updatePatterns("food", data, svg); // Pass the ID of the clicked button
// });
