document.querySelector('.registration-form').addEventListener('submit', function (e) {
    e.preventDefault(); // Prevent default form submission behavior

    const form = e.target;
    const formData = new FormData(form);

    // Clear previous messages
    const messageContainer = document.querySelector('.form-messages');
    messageContainer.textContent = 'Processing...';
    messageContainer.style.color = 'blue';

    // AJAX request using Fetch API
    fetch('./assets/php/formhandler.php', {
        method: 'POST',
        body: formData,
    })
        .then(response => {
            // Check if the response is okay
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json(); // Parse the JSON response
        })
        .then(data => {
            if (data.success) {
                // Show success message
                messageContainer.textContent = data.message;
                messageContainer.style.color = 'green';
                alert(`Success: ${data.message}`); // Display success alert
                form.reset(); // Reset the form
            } else {
                // Show error message from the server
                messageContainer.textContent = data.message;
                messageContainer.style.color = 'red';
                alert(`Error: ${data.message}`); // Display error alert
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // Show a generic error message
            messageContainer.textContent = 'An error occurred while processing your request. Please try again.';
            messageContainer.style.color = 'red';
            alert('An error occurred. Please try again later.'); // Display generic error alert
        });
});
