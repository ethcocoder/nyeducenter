<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Book Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Full-page loader styles */
        #fullPageLoader {
            display: none;
            position: fixed;
            top:0; left:0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1050;
            color: #fff;
            text-align: center;
        }
        #fullPageLoader .loader-content {
            position: absolute;
            top:50%; left:50%;
            transform: translate(-50%, -50%);
        }
        #fullPageLoader .progress {
            width: 300px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container mt-5">

        <!-- Full Page Loader -->
        <div id="fullPageLoader">
            <div class="loader-content">
                <h3 id="loaderText">Uploading...</h3>
                <div class="progress">
                    <div id="loaderProgress" class="progress-bar" role="progressbar" style="width: 0%">0%</div>
                </div>
            </div>
        </div>

        <!-- Books Section -->
        <div id="books-section" class="content-section books-section">
            <h2 class="mb-4">
                <i class="fas fa-book me-2"></i> Book Management
            </h2>
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    All Books
                    <button class="btn btn-primary btn-sm" onclick="showAddBookModal()">
                        <i class="fas fa-plus me-2"></i> Add Book
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Title</th>
                                    <th>Author</th>
                                    <th>Category</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="booksTableBody">
                                <!-- Book data will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Book Modal -->
        <div class="modal fade" id="addBookModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Book</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="addBookForm" enctype="multipart/form-data">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="bookTitle" class="form-label">Book Title</label>
                                <input type="text" class="form-control" id="bookTitle" name="bookTitle" required>
                            </div>
                            <div class="mb-3">
                                <label for="bookAuthor" class="form-label">Author (Optional)</label>
                                <input type="text" class="form-control" id="bookAuthor" name="bookAuthor">
                            </div>
                            <div class="mb-3">
                                <label for="bookCategory" class="form-label">Category</label>
                                <select class="form-select" id="bookCategory" name="bookCategory" required>
                                    <!-- Categories loaded dynamically -->
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="bookDescription" class="form-label">Description (Optional)</label>
                                <textarea class="form-control" id="bookDescription" name="bookDescription" rows="3"></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Book Source</label>
                                <div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="bookSourceType" value="upload" checked>
                                        <label class="form-check-label">Upload PDF</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="bookSourceType" value="link">
                                        <label class="form-check-label">Add Link</label>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3" id="bookFileUpload">
                                <label for="bookFile" class="form-label">Upload PDF File</label>
                                <input type="file" class="form-control" id="bookFile" name="bookFile" accept=".pdf">
                            </div>
                            <div class="mb-3" id="bookLinkInput">
                                <label for="bookLink" class="form-label">Book Link (URL)</label>
                                <input type="url" class="form-control" id="bookLink" name="bookLink" placeholder="https://example.com/book.pdf">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Add Book</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Book Modal -->
        <div class="modal fade" id="editBookModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Book</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="editBookForm" enctype="multipart/form-data">
                        <div class="modal-body">
                            <input type="hidden" id="editBookId" name="bookId">
                            <div class="mb-3">
                                <label for="editBookTitle" class="form-label">Book Title</label>
                                <input type="text" class="form-control" id="editBookTitle" name="bookTitle" required>
                            </div>
                            <div class="mb-3">
                                <label for="editBookAuthor" class="form-label">Author (Optional)</label>
                                <input type="text" class="form-control" id="editBookAuthor" name="bookAuthor">
                            </div>
                            <div class="mb-3">
                                <label for="editBookCategory" class="form-label">Category</label>
                                <select class="form-select" id="editBookCategory" name="bookCategory" required></select>
                            </div>
                            <div class="mb-3">
                                <label for="editBookDescription" class="form-label">Description (Optional)</label>
                                <textarea class="form-control" id="editBookDescription" name="bookDescription" rows="3"></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Book Source</label>
                                <div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="editBookSourceType" value="upload">
                                        <label class="form-check-label">Upload PDF</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="editBookSourceType" value="link">
                                        <label class="form-check-label">Add Link</label>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3" id="editBookFileUpload">
                                <label for="editBookFile" class="form-label">Upload New PDF File (Optional)</label>
                                <input type="file" class="form-control" id="editBookFile" name="bookFile" accept=".pdf">
                            </div>
                            <div class="mb-3" id="editBookLinkInput">
                                <label for="editBookLink" class="form-label">Book Link (URL)</label>
                                <input type="url" class="form-control" id="editBookLink" name="bookLink">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Loader functions
        function showFullPageLoader(text){
            $('#loaderText').text(text);
            $('#loaderProgress').css('width','0%').text('0%');
            $('#fullPageLoader').fadeIn();
        }
        function hideFullPageLoader(){
            $('#fullPageLoader').fadeOut();
        }
        function updateLoaderProgress(percent){
            $('#loaderProgress').css('width',percent+'%').text(percent+'%');
        }

        function handleRequiredAttributes(modalId, sourceType){
            const $modal = $(modalId);
            if(sourceType==='upload'){
                $modal.find('input[type="file"]').prop('required', true);
                $modal.find('input[type="url"]').prop('required', false);
            } else {
                $modal.find('input[type="file"]').prop('required', false);
                $modal.find('input[type="url"]').prop('required', true);
            }
        }

        $(document).ready(function(){

            // Toggle required on source type change
            $('#addBookModal input[name="bookSourceType"]').change(function(){
                handleRequiredAttributes('#addBookModal', $(this).val());
            });
            $('#editBookModal input[name="editBookSourceType"]').change(function(){
                handleRequiredAttributes('#editBookModal', $(this).val());
            });

            handleRequiredAttributes('#addBookModal', $('#addBookModal input[name="bookSourceType"]:checked').val());

            $('#editBookModal').on('show.bs.modal', function(){
                const currentLink = $('#editBookLink').val();
                if(currentLink && currentLink!==''){
                    $('#editBookModal input[name="editBookSourceType"][value="link"]').prop('checked', true);
                    handleRequiredAttributes('#editBookModal','link');
                } else {
                    $('#editBookModal input[name="editBookSourceType"][value="upload"]').prop('checked', true);
                    handleRequiredAttributes('#editBookModal','upload');
                }
            });

            // AJAX form submissions
            function ajaxFormSubmit(formId, url){
                $(formId).submit(function(e){
                    e.preventDefault();
                    const form = this;
                    const formData = new FormData(form);
                    const sourceType = $(form).find('input[type="radio"]:checked').val();

                    if(sourceType==='upload' && formData.get('bookFile').name){
                        showFullPageLoader('Uploading PDF...');
                    }

                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: formData,
                        contentType: false,
                        processData: false,
                        xhr: function(){
                            const xhr = new window.XMLHttpRequest();
                            xhr.upload.addEventListener("progress", function(evt){
                                if(evt.lengthComputable && sourceType==='upload'){
                                    const percent = Math.round((evt.loaded / evt.total)*100);
                                    updateLoaderProgress(percent);
                                }
                            });
                            return xhr;
                        },
                        success: function(data){
                            hideFullPageLoader();
                            if(data.success){
                                Swal.fire({icon:'success', title:'Success', showConfirmButton:false, timer:1500});
                                bootstrap.Modal.getInstance($(form).closest('.modal')[0]).hide();
                                loadBooks();
                            } else {
                                Swal.fire({icon:'error', title:'Error', text:data.message});
                            }
                        },
                        error: function(err){
                            hideFullPageLoader();
                            Swal.fire({icon:'error', title:'Error', text:'An unexpected error occurred.'});
                        }
                    });
                });
            }

            ajaxFormSubmit('#addBookForm','../../api/add-book.php');
            ajaxFormSubmit('#editBookForm','../../api/admin-update-book.php');

            // Load books
            function loadBooks(){
                $.getJSON('../../api/admin-get-books.php', function(data){
                    const tbody = $('#booksTableBody');
                    tbody.empty();
                    if(data.success && data.books.length>0){
                        data.books.forEach(book=>{
                            tbody.append(`<tr>
                                <td>${book.id}</td>
                                <td>${book.title}</td>
                                <td>${book.author}</td>
                                <td>${book.grade_name}</td>
                                <td>
                                    <button class="btn btn-sm btn-primary" onclick='populateEditBookModal(${JSON.stringify(book)})'>Edit</button>
                                    <button class="btn btn-sm btn-danger" onclick='deleteBook(${book.id})'>Delete</button>
                                </td>
                            </tr>`);
                        });
                    } else {
                        tbody.append('<tr><td colspan="5">No books available.</td></tr>');
                    }
                });
            }

            window.populateEditBookModal = function(book){
                $('#editBookId').val(book.id);
                $('#editBookTitle').val(book.title);
                $('#editBookAuthor').val(book.author);
                $('#editBookCategory').val(book.category_id);
                $('#editBookDescription').val(book.description);
                $('#editBookLink').val(book.link || '');
                $('#editBookModal').modal('show');
            }

            loadBooks();

            // Show add book modal
            window.showAddBookModal = function(){
                $('#addBookForm')[0].reset();
                handleRequiredAttributes('#addBookModal','upload');
                $('#addBookModal').modal('show');
            }

        });
    </script>
</body>
</html>
