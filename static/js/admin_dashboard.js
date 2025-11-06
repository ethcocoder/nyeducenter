// ===================================
// ADMIN DASHBOARD JAVASCRIPT
// ===================================

// Initialize Sortable.js for drag-and-drop
function initializeSortable() {
    // Initialize module sorting
    const modulesList = document.getElementById('modulesList');
    if (modulesList) {
        Sortable.create(modulesList, {
            animation: 150,
            ghostClass: 'sortable-ghost',
            handle: '.module-drag-handle',
            onEnd: function(evt) {
                reorderModules();
            }
        });
    }

    // Initialize submodule sorting for each module
    document.querySelectorAll('.submodules-list').forEach(submodulesList => {
        Sortable.create(submodulesList, {
            animation: 150,
            ghostClass: 'sortable-ghost',
            handle: '.submodule-drag-handle',
            onEnd: function(evt) {
                const moduleId = submodulesList.dataset.moduleId;
                const parentId = submodulesList.dataset.parentId;
                reorderSubmodules(moduleId, parentId);
            }
        });
    });

    // Initialize content items sorting for each submodule
    document.querySelectorAll('.content-items-list').forEach(contentList => {
        Sortable.create(contentList, {
            animation: 150,
            ghostClass: 'sortable-ghost',
            handle: '.content-drag-handle',
            onEnd: function(evt) {
                const submoduleId = contentList.dataset.submoduleId;
                reorderContentItems(submoduleId);
            }
        });
    });
}


    initializeSortable();



    // Create Module
    document.getElementById('createModuleBtn')?.addEventListener('click', function() {
        const modal = new bootstrap.Modal(document.getElementById('createModuleModal'));
        modal.show();
    });

    document.getElementById('createModuleForm')?.addEventListener('submit', async function(e) {
        e.preventDefault();
        const title = document.getElementById('moduleTitle').value;

        try {
            const response = await fetch('/admin/module/create', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ title: title })
            });

            const data = await response.json();
            if (data.status === 'success') {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred while creating the module.');
        }
    });

// Rename Module
function renameModule(moduleId) {
    const newTitle = prompt('Enter new module title:');
    if (newTitle && newTitle.trim()) {
        fetch(`/admin/module/${moduleId}/rename`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ new_title: newTitle })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while renaming the module.');
        });
    }
}

// Delete Module
function deleteModule(moduleId) {
    if (confirm('Are you sure you want to delete this module? This action cannot be undone.')) {
        fetch(`/admin/module/${moduleId}/delete`, {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the module.');
        });
    }
}

// Duplicate Module
function duplicateModule(moduleId) {
    if (confirm('Duplicate this module?')) {
        fetch(`/admin/module/${moduleId}/duplicate`, {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while duplicating the module.');
        });
    }
}

// Toggle Publish Module
document.querySelectorAll('.btn-toggle-publish').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        const moduleCard = this.closest('.module-card');
        const moduleId = moduleCard.dataset.moduleId;

        fetch(`/admin/module/${moduleId}/toggle-publish`, {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while toggling publish status.');
        });
    });
});

// Reorder Modules
function reorderModules() {
    const modulesList = document.getElementById('modulesList');
    const newOrder = Array.from(modulesList.querySelectorAll('.module-card')).map(card => card.dataset.moduleId);

    fetch('/admin/modules/reorder', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ new_order: newOrder })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status !== 'success') {
            alert('Error: ' + data.message);
            location.reload();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while reordering modules.');
        location.reload();
    });
}

// ===================================
// SUBMODULE OPERATIONS
// ===================================

// Create Submodule
document.querySelectorAll('.create-submodule-form').forEach(form => {
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        const titleInput = this.querySelector('input[type="text"]');
        const title = titleInput.value.trim();
        const moduleId = this.dataset.moduleId;
        const parentId = this.dataset.parentId;

        if (!title) {
            alert('Submodule title cannot be empty.');
            return;
        }

        console.log('Creating submodule with:', { title, moduleId, parentId });

        try {
            const response = await fetch('/admin/submodule/create', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ title, module_id: moduleId, parent_id: parentId })
            });

            const data = await response.json();
            if (data.status === 'success') {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred while creating the submodule.');
        }
    });
});



// Rename Submodule
function renameSubmodule(submoduleId) {
    const newTitle = prompt('Enter new submodule title:');
    if (newTitle && newTitle.trim()) {
        fetch(`/admin/submodule/${submoduleId}/rename`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ new_title: newTitle })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while renaming the submodule.');
        });
    }
}

// Delete Submodule
function deleteSubmodule(submoduleId) {
    if (confirm('Are you sure you want to delete this submodule? This action cannot be undone.')) {
        fetch(`/admin/submodule/${submoduleId}/delete`, {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the submodule.');
        });
    }
}

// Duplicate Submodule
function duplicateSubmodule(submoduleId) {
    if (confirm('Duplicate this submodule?')) {
        fetch(`/admin/submodule/${submoduleId}/duplicate`, {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while duplicating the submodule.');
        });
    }
}

// Reorder Submodules
function reorderSubmodules(moduleId, parentId) {
    const submodulesList = document.querySelector(`.submodules-list[data-module-id="${moduleId}"][data-parent-id="${parentId}"]`);
    const newOrder = Array.from(submodulesList.querySelectorAll('.submodule-card')).map(card => card.dataset.submoduleId);

    const body = { new_order: newOrder };
    if (parentId) {
        body.parent_id = parentId;
    } else {
        body.module_id = moduleId;
    }

    fetch('/admin/submodules/reorder', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(body)
    })
    .then(response => response.json())
    .then(data => {
        if (data.status !== 'success') {
            alert('Error: ' + data.message);
            location.reload();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while reordering submodules.');
        location.reload();
    });
}

// ===================================
// CONTENT ITEM OPERATIONS
// ===================================

document.querySelectorAll('.create-content-form').forEach(form => {
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        const submoduleId = this.dataset.submoduleId;
        const contentType = this.querySelector('select[name="content_type"]').value;
        const contentId = this.querySelector('select[name="content_id"]').value;

        console.log('Creating content item with:', { submoduleId, contentType, contentId });

        try {
            const response = await fetch('/admin/item/create', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    submodule_id: submoduleId,
                    content_type: contentType,
                    content_id: contentId
                })
            });

            const data = await response.json();
            if (data.status === 'success') {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred while adding the content item.');
        }
    });
});

// Load content based on type
document.querySelectorAll('select[name="content_type"]').forEach(select => {
    select.addEventListener('change', async function() {
        const contentType = this.value;
        const contentIdSelect = this.closest('form').querySelector('select[name="content_id"]');
        contentIdSelect.innerHTML = '<option value="">Loading...</option>';

        if (!contentType) {
            contentIdSelect.innerHTML = '<option value="">Select content...</option>';
            return;
        }

        try {
            const response = await fetch(`/admin/content/list/${contentType}`);
            const data = await response.json();

            contentIdSelect.innerHTML = '<option value="">Select content...</option>';
            if (data.items && data.items.length > 0) {
                data.items.forEach(item => {
                    const option = document.createElement('option');
                    option.value = item.id;
                    option.textContent = item.title;
                    contentIdSelect.appendChild(option);
                });
            } else {
                contentIdSelect.innerHTML = '<option value="">No ' + contentType + 's available</option>';
            }
        } catch (error) {
            console.error('Error:', error);
            contentIdSelect.innerHTML = '<option value="">Error loading content</option>';
        }
    });
});

// Unlink Content Item
function unlinkContentItem(itemId) {
    if (confirm('Are you sure you want to unlink this content item? This will not delete the content itself.')) {
        fetch(`/admin/item/${itemId}/unlink`, {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while unlinking the content item.');
        });
    }
}

// Delete Content
function deleteContent(contentType, contentId) {
    if (confirm('Are you sure you want to permanently delete this content? This action cannot be undone.')) {
        fetch(`/admin/content/${contentType}/${contentId}/delete`, {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the content.');
        });
    }
}

    // Reorder Content Items
function reorderContentItems(submoduleId) {
    const contentList = document.querySelector(`.content-items-list[data-submodule-id="${submoduleId}"]`);
    const newOrder = Array.from(contentList.querySelectorAll('.content-item')).map(item => item.dataset.itemId);

    fetch('/admin/items/reorder', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ new_order: newOrder, parent_id: submoduleId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status !== 'success') {
            alert('Error: ' + data.message);
            location.reload();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while reordering content items.');
        location.reload();
    });
}

// Function to fetch and populate content_id dropdown
async function populateContentIdSelect(selectedContentType, contentIdSelect) {
    contentIdSelect.innerHTML = '<option value="">-- Select Content --</option>'; // Clear previous options
    if (!selectedContentType) {
        return;
    }

    try {
        const response = await fetch(`/admin/content/list/${selectedContentType}`);
        const data = await response.json();

        if (data.status === 'success') {
            data.items.forEach(item => {
                const option = document.createElement('option');
                option.value = item.id;
                option.textContent = item.title;
                contentIdSelect.appendChild(option);
            });
        } else {
            alert('Error fetching content: ' + data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred while fetching content.');
    }
}

// ===================================
// EVENT LISTENERS FOR DELETE BUTTONS
// ===================================

// Unlink Content Item
function unlinkContentItem(itemId) {
    if (confirm('Are you sure you want to unlink this content item? This will not delete the content itself.')) {
        fetch(`/admin/item/${itemId}/unlink`, {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while unlinking the content item.');
        });
    }
}

// Delete Content
function deleteContent(contentType, contentId) {
    if (confirm('Are you sure you want to permanently delete this content? This action cannot be undone.')) {
        fetch(`/admin/content/${contentType}/${contentId}/delete`, {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the content.');
        });
    }
}

document.querySelectorAll('.btn-unlink-content').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        const itemId = this.dataset.itemId;
        unlinkContentItem(itemId);
    });
});

document.querySelectorAll('.btn-delete-content').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        const contentType = this.dataset.contentType;
        const contentId = this.dataset.contentId;
        deleteContent(contentType, contentId);
    });
});

// ===================================
// INITIALIZATION
// ===================================

document.addEventListener('DOMContentLoaded', function() {
    initializeSortable();

    const addContentForm = document.getElementById('add-content-form');
    if (addContentForm) {
        const contentTypeSelect = document.getElementById('content-type-select');
        const contentIdSelect = document.getElementById('content-id-select');
        const submoduleId = addContentForm.dataset.submoduleId;

        // Event listener for content type selection change
        contentTypeSelect.addEventListener('change', function() {
            populateContentIdSelect(this.value, contentIdSelect);
        });

        // Event listener for add content form submission
        addContentForm.addEventListener('submit', async function(event) {
            event.preventDefault();

            const content_type = contentTypeSelect.value;
            const content_id = contentIdSelect.value;

            if (!content_type || !content_id) {
                alert('Please select both content type and content.');
                return;
            }

            try {
                const response = await fetch('/admin/item/create', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        submodule_id: submoduleId,
                        content_type: content_type,
                        content_id: content_id
                    })
                });
                const data = await response.json();

                if (data.status === 'success') {
                    alert('Content item added successfully!');
                    location.reload(); 
                } else {
                    alert('Error adding content: ' + data.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred while adding content.');
            }
        });
    }
});