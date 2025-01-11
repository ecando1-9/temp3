// assets/js/chat.js

document.addEventListener('DOMContentLoaded', function () {
    // Elements
    const searchInput = document.getElementById('searchUser');
    const searchResults = document.getElementById('searchResults');
    const searchHistoryList = document.getElementById('searchHistoryList');
    const clearSearchHistoryButton = document.getElementById('clearSearchHistory');
    const floatButton = document.getElementById('floatButton');
    const chatArea = document.querySelector('.chat-area7'); // Update selector as needed
    const messageInput = document.getElementById('messageInput'); // Update selector as needed

    /**
     * User Search Functionality
     */
    searchInput.addEventListener('input', debounce(function () {
        let query = this.value.trim();

        // Basic sanitization to prevent XSS
        query = query.replace(/[<>]/g, '');

        if (query.length > 0) {
            fetch(`search.php?query=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    let usersHtml = '';
                    if (data.length > 0) {
                        data.forEach(user => {
                            usersHtml += `<div class="user" data-user-id="${user.id}">${user.name}</div>`;
                        });
                    } else {
                        usersHtml = '<div class="no-results">No users found.</div>';
                    }
                    searchResults.innerHTML = usersHtml;

                    // Add click event for each user in search results
                    document.querySelectorAll('.user').forEach(userDiv => {
                        userDiv.addEventListener('click', function () {
                            const userId = this.getAttribute('data-user-id');
                            window.location.href = `user_chat.php?user_id=${userId}`;
                        });
                    });

                    // Save the search term to history
                    if (query.length > 0) {
                        saveSearchTerm(query);
                    }
                })
                .catch(error => {
                    console.error('Error fetching search results:', error);
                });
        } else {
            searchResults.innerHTML = '';
        }
    }, 300)); // Debounce with 300ms delay

    /**
     * Debounce Function to Limit API Calls
     */
    function debounce(func, delay) {
        let debounceTimer;
        return function () {
            const context = this;
            const args = arguments;
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => func.apply(context, args), delay);
        };
    }

    /**
     * Search History Management
     */
    // Function to save the search term to local storage
    function saveSearchTerm(searchTerm) {
        let searchHistory = JSON.parse(localStorage.getItem('searchHistory')) || [];
        if (!searchHistory.includes(searchTerm)) {
            searchHistory.unshift(searchTerm); // Add to the beginning
            if (searchHistory.length > 5) searchHistory.pop(); // Limit to 5 entries
            localStorage.setItem('searchHistory', JSON.stringify(searchHistory));
            showSearchHistory(); // Update the displayed history
        }
    }

    // Function to show recent search history
    function showSearchHistory() {
        const searchHistory = JSON.parse(localStorage.getItem('searchHistory')) || [];
        searchHistoryList.innerHTML = ''; // Clear the list first

        if (searchHistory.length > 0) {
            searchHistory.forEach(term => {
                const li = document.createElement('li');
                li.textContent = term;
                li.addEventListener('click', () => {
                    searchInput.value = term; // Autofill search term
                    performSearch(term); // Perform the search
                });
                searchHistoryList.appendChild(li);
            });
        } else {
            const li = document.createElement('li');
            li.textContent = 'No recent searches';
            searchHistoryList.appendChild(li);
        }
    }

    // Function to perform search (triggers the input event)
    function performSearch(term) {
        searchInput.value = term;
        const event = new Event('input');
        searchInput.dispatchEvent(event);
    }

    // Event listener to clear search history
    clearSearchHistoryButton.addEventListener('click', () => {
        localStorage.removeItem('searchHistory');
        showSearchHistory(); // Refresh history display
        searchResults.innerHTML = ''; // Clear any displayed search results
        alert('Search history has been cleared.');
    });

    /**
     * Delete Chat Functionality
     */
    // Function to delete a chat
    function deleteChat(chatUserId, chatItem) {
        fetch('delete_chat.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `chat_user_id=${chatUserId}`
        })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    chatItem.remove(); // Remove the chat from the UI
                    alert('Chat deleted successfully');
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }

    // Add event listeners to all existing delete buttons
    document.querySelectorAll('.delete-chat').forEach(button => {
        button.addEventListener('click', function () {
            const chatUserId = this.getAttribute('data-chat-id');
            const chatItem = this.closest('.recent-chat-item');

            if (confirm('Are you sure you want to delete this chat?')) {
                deleteChat(chatUserId, chatItem);
            }
        });
    });

    /**
     * Floating Button Functionality
     */
    if (floatButton && chatArea && messageInput) {
        floatButton.addEventListener('click', function () {
            // Scroll to the bottom of the chat area smoothly
            chatArea.scrollTo({
                top: chatArea.scrollHeight,
                behavior: 'smooth'
            });

            // Focus on the message input after scrolling
            setTimeout(() => {
                messageInput.focus();
            }, 500); // Adjust delay as needed
        });
    }

    /**
     * Chat Updates: Update Unread Counts Periodically
     */
    // Function to update unread message counts
    function updateUnreadCounts() {
        fetch('fetch_unread_counts.php')
            .then(response => response.json())
            .then(data => {
                data.forEach(chat => {
                    const chatItem = document.querySelector(`.recent-chat-item[data-chat-id="${chat.chat_user_id}"]`);
                    if (chatItem) {
                        let unreadElement = chatItem.querySelector('.unread-count');

                        if (chat.unread_count > 0) {
                            if (!unreadElement) {
                                // Create the unread count element if it doesn't exist
                                unreadElement = document.createElement('span');
                                unreadElement.classList.add('unread-count');
                                unreadElement.textContent = chat.unread_count;

                                // Append the unread count next to the chat name
                                const chatName = chatItem.querySelector('.chat-name');
                                chatName.appendChild(unreadElement);
                            } else {
                                // Update the existing unread count
                                unreadElement.textContent = chat.unread_count;
                            }
                        } else {
                            // Remove the unread count element if there are no unread messages
                            if (unreadElement) {
                                unreadElement.remove();
                            }
                        }
                    }
                });
            })
            .catch(error => {
                console.error('Error fetching unread counts:', error);
            });
    }

    // Initialize chat updates
    function initializeChatUpdates() {
        updateUnreadCounts(); // Initial update

        // Set interval to update every 5 seconds (5000 milliseconds)
        setInterval(() => {
            updateUnreadCounts();
        }, 5000);
    }

    // Start the chat updates
    initializeChatUpdates();
});
