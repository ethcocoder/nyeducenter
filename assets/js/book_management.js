function showAddBookModal() {
    var addBookModal = new bootstrap.Modal(document.getElementById('addBookModal'));
    addBookModal.show();
}

function editBook(bookId) {
    fetch(`../../api/admin-get-book.php?id=${bookId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                document.getElementById('editBookId').value = data.book.id;
                document.getElementById('editBookTitle').value = data.book.title;
                document.getElementById('editBookAuthor').value = data.book.author;
                document.getElementById('editBookCategory').value = data.book.category_id;
                document.getElementById('editBookDescription').value = data.book.description;
                var editBookModal = new bootstrap.Modal(document.getElementById('editBookModal'));
                editBookModal.show();
            } else {
                Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to fetch book data: ' + (data.message || 'Unknown error')
            });
            }
        })
        .catch(error => {
            console.error('Error fetching book data:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred while fetching book data.'
            });
        });
}

function deleteBook(bookId) {
    if (confirm('Are you sure you want to delete this book?')) {
        fetch('../../api/admin-delete-book.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ book_id: bookId })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                Swal.fire({
                icon: 'success',
                title: 'Success',
                text: 'Book deleted successfully',
                showConfirmButton: false,
                timer: 1500
            });
                loadBooks(); // Reload books after deletion
            } else {
                Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to delete book: ' + (data.message || 'Unknown error')
            });
            }
        })
        .catch(error => {
            console.error('Error deleting book:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred while deleting the book.'
            });
        });
    }
}

function loadBooks() {
    fetch('../../api/admin-get-books.php')
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById('booksTableBody');
            tbody.innerHTML = '';
            
            if (data.books && Array.isArray(data.books)) {
                data.books.forEach(book => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${book.id}</td>
                        <td>${book.title}</td>
                        <td>${book.author || 'N/A'}</td>
                        <td>${book.category_name}</td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn btn-sm btn-primary" onclick="editBook(${book.id})">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deleteBook(${book.id})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    `;
                    tbody.appendChild(row);
                });
            } else {
                tbody.innerHTML = '<tr><td colspan="5">No books available.</td></tr>';
            }
        })
        .catch(error => {
            console.error('Error loading books:', error);
        });
}