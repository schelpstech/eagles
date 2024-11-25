document.querySelector('.registration-form').addEventListener('submit', function (e) {
    e.preventDefault(); // Prevent default form submission behavior

    const form = e.target;
    const formData = new FormData(form);
    const messageContainer = document.querySelector('.form-messages');
    const submitButton = form.querySelector('button[type="submit"]');
    let isValid = true;

    // Reset previous messages
    messageContainer.textContent = '';
    messageContainer.style.color = '';

    // Helper function to show alerts
    function showAlert(message) {
        alert(message);
        isValid = false;
    }

    // Field validations
    const requiredFields = [
        { name: 'fullname', errorMessage: 'Full name is required.' },
        { name: 'gender', errorMessage: 'Gender is required.' },
        { name: 'title', errorMessage: 'Title is required.' },
        { name: 'position', errorMessage: 'Position is required.' },
        { name: 'department', errorMessage: 'Department is required.' },
        { name: 'participation_mode', errorMessage: 'Participation mode is required.' },
        { name: 'phone', errorMessage: 'Phone number is required.' },
        { name: 'church_name', errorMessage: 'Church name is required.' },
    ];

    requiredFields.forEach(field => {
        const input = form.querySelector(`[name="${field.name}"]`);
        if (!input || !input.value.trim()) {
            showAlert(field.errorMessage);
        }
    });

    // Validate phone number
    const phoneInput = form.querySelector('[name="phone"]');
    if (phoneInput) {
        const phone = phoneInput.value.trim();
        if (!/^\d{11}$/.test(phone)) {
            showAlert('Phone number must be exactly 11 digits.');
        }
    }

    // Validate church name
    const churchNameInput = form.querySelector('[name="church_name"]');
    if (churchNameInput) {
        const churchName = churchNameInput.value.trim();
        const wordCount = churchName.split(/\s+/).length;

        if (wordCount < 4) {
            showAlert('Church name must be written in full (at least 4 words).');
        }
    }

    // Validate email (not required, but must be valid if filled)
    const emailInput = form.querySelector('[name="email"]');
    if (emailInput) {
        const email = emailInput.value.trim();
        if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            showAlert('Please enter a valid email address.');
        }
    }

    // Stop submission if validation fails
    if (!isValid) {
        messageContainer.textContent = 'Please fill in all required fields and correct errors.';
        messageContainer.style.color = 'red';
        return;
    }

    // Proceed with submission
    submitButton.disabled = true;
    submitButton.textContent = 'Submitting...';

    fetch('./assets/php/formhandler.php', {
        method: 'POST',
        body: formData,
    })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                messageContainer.textContent = data.message || 'Registration successful!';
                messageContainer.style.color = 'green';
                alert(`Success: ${data.message || 'Registration successful!'}`);
                form.reset();
            } else {
                messageContainer.textContent = data.message || 'An error occurred. Please try again.';
                messageContainer.style.color = 'red';
                alert(`Error: ${data.message || 'An error occurred. Please try again.'}`);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            messageContainer.textContent = 'An error occurred while processing your request. Please try again later.';
            messageContainer.style.color = 'red';
            alert('An error occurred. Please try again later.');
        })
        .finally(() => {
            submitButton.disabled = false;
            submitButton.textContent = 'Register Now';
        });
});
