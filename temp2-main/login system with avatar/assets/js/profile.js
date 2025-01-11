document.addEventListener('DOMContentLoaded', function() {
    const blockButton = document.querySelector('button[name="action"]');
    const userId = new URLSearchParams(window.location.search).get('user_id');

    // Check if the block button exists
    if (blockButton) {
        blockButton.addEventListener('click', function(event) {
            event.preventDefault(); // Prevent the default form submission

            // Determine action based on button value
            const action = blockButton.value;
            const form = blockButton.closest('form');

            // Create a FormData object to send data
            const formData = new FormData();
            formData.append('action', action);
            
            // Make the request to the server
            fetch(form.action, {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(result => {
                if (result.includes('Unblock User')) {
                    // Update button text and value if unblocked
                    blockButton.textContent = 'Block User';
                    blockButton.value = 'block';
                } else if (result.includes('Block User')) {
                    // Update button text and value if blocked
                    blockButton.textContent = 'Unblock User';
                    blockButton.value = 'unblock';
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    }
});
