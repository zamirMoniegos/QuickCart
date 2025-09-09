<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION["admin_logged_in"])) {
    header("Location: login.php");
    exit;
}

$name = $_SESSION['admin_name'] ?? 'Admin';
$initial = !empty($name) ? strtoupper($name[0]) : 'A';
$profilePic = $_SESSION['picture'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Admin - Product Management</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <style>
        .btn { transition: all 0.2s ease; }
        .btn:hover, .btn:focus { box-shadow: 0 4px 10px rgba(0,0,0,0.15); transform: translateY(-2px); outline: none; }
        .content-wrapper { padding: 2rem; }
        .form-container { display: none; margin-bottom: 2rem; padding: 1.5rem; border: 1px solid #ddd; border-radius: 0.5rem; background-color: #f9f9f9; }
        .initial-avatar { width: 34px; height: 34px; background-color: #007bff; color: white; display:flex; align-items:center; justify-content:center; font-weight: bold; border-radius: 50%; }
        .main-sidebar .nav-link:hover { background-color: rgba(255,255,255,0.1); transform: translateX(5px); }
        .table-warning, .table-warning > th, .table-warning > td { background-color: #fff3cd !important; }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <ul class="navbar-nav"><li class="nav-item"><a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a></li></ul>
    </nav>

    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <a href="admin.php" class="brand-link text-center"><span class="brand-text font-weight-light">QuickCart Admin</span></a>
        <div class="sidebar">
            <div class="user-panel mt-3 pb-3 mb-3 d-flex align-items-center">
                <div class="image">
                    <?php if ($profilePic): ?>
                        <img src="<?= htmlspecialchars($profilePic) ?>" class="img-circle elevation-2" alt="User">
                    <?php else: ?>
                        <div class="initial-avatar elevation-2"><?= $initial ?></div>
                    <?php endif; ?>
                </div>
                <div class="info"><a href="#" class="d-block"><?= htmlspecialchars($name) ?></a></div>
            </div>
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" role="menu">
                    <li class="nav-item"><a href="admin.php" class="nav-link active"><i class="nav-icon fas fa-box"></i> <p>Products</p></a></li>
                    <li class="nav-item"><a href="logout.php" class="nav-link"><i class="nav-icon fas fa-sign-out-alt"></i> <p>Logout</p></a></li>
                </ul>
            </nav>
        </div>
    </aside>

    <div class="content-wrapper">
        <h3>Product Management</h3>
        <p>Manage your product inventory here.</p>
        
        <div class="row mb-3">
            <div class="col-md-6">
                <button id="showAddFormBtn" class="btn btn-primary"><i class="fas fa-plus"></i> Add New Product</button>
            </div>
        </div>

        <div id="formContainer" class="form-container">
            <h4 id="formTitle">Add Product</h4>
            <form id="productForm" autocomplete="off">
                <input name="action" id="formAction" value="add" type="hidden">
                <input name="id" id="productId" type="hidden">
                <div class="form-row">
                    <div class="form-group col-md-4"><label for="barcode">Barcode</label><input name="barcode" id="barcode" class="form-control" required></div>
                    <div class="form-group col-md-5"><label for="name">Name</label><input name="name" id="name" class="form-control" required></div>
                    <div class="form-group col-md-3"><label for="price">Price (₱)</label><input type="number" name="price" id="price" step="0.01" class="form-control" required></div>
                </div>
                <button type="submit" class="btn btn-success" id="formSubmitBtn">Add Product</button>
                <button type="button" class="btn btn-secondary" id="cancelFormBtn">Cancel</button>
            </form>
        </div>

        <div class="table-responsive">
            <table id="productTable" class="table table-bordered table-striped" style="width:100%">
                <thead><tr><th>ID</th><th>Barcode</th><th>Name</th><th>Price</th><th>Actions</th></tr></thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="confirmModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmModalTitle"></h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body" id="confirmModalBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn" id="confirmModalBtn">Confirm</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

<script>
$(document).ready(function() {
    toastr.options = { "positionClass": "toast-bottom-right", "timeOut": 3000 };
    
    let table = $('#productTable').DataTable({
        responsive: true,
        ajax: { url: 'get-products.php', dataSrc: 'data' },
        columns: [
            { data: 'id' }, { data: 'barcode' }, { data: 'name' },
            { data: 'price', render: data => '₱' + parseFloat(data).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",") },
            { data: null, orderable: false, render: () => `
                <button class="btn btn-sm btn-info btn-edit" title="Edit"><i class="fas fa-edit"></i></button>
                <button class="btn btn-sm btn-danger btn-delete" title="Delete"><i class="fas fa-trash"></i></button>`
            }
        ]
    });

    // --- FORM LOGIC ---
    function resetAndHideForm() {
        $('#productForm')[0].reset();
        $('#formAction').val('add');
        $('#formTitle').text('Add Product');
        $('#formSubmitBtn').text('Add Product').removeClass('btn-warning').addClass('btn-success');
        $('#formContainer').slideUp();
        table.rows().nodes().to$().removeClass('table-warning');
    }

    function showFormForEdit(data) {
        // We don't call resetAndHideForm here to prevent slideUp animation
        $('#productForm')[0].reset();
        table.rows().nodes().to$().removeClass('table-warning');
        
        $('#formTitle').text('Edit Product');
        $('#formSubmitBtn').text('Update Product').removeClass('btn-success').addClass('btn-warning');
        $('#formAction').val('update');
        $('#productId').val(data.id);
        $('#barcode').val(data.barcode);
        $('#name').val(data.name);
        $('#price').val(data.price);
        $('#formContainer').slideDown();
        $('html, body').animate({ scrollTop: $('#formContainer').offset().top - 20 }, 500);
    }

    $('#showAddFormBtn').click(() => { 
        resetAndHideForm(); 
        $('#formContainer').slideDown(); 
        $('#barcode').focus(); 
    });
    
    // --- NEW: Confirmation on Cancel ---
    $('#cancelFormBtn').click(function() {
        // Check if the form is in 'update' (edit) mode
        if ($('#formAction').val() === 'update') {
            // If editing, show a confirmation modal before canceling
            $('#confirmModalTitle').text('Discard Changes?');
            $('#confirmModalBody').text('You have unsaved changes. Are you sure you want to discard them?');
            $('#confirmModalBtn')
                .text('Discard')
                .removeClass('btn-danger') // Ensure it's not red from a delete action
                .addClass('btn-warning');   // Make it yellow for caution
            
            $('#confirmModal').modal('show');

            // Handle the confirmation click
            $('#confirmModalBtn').one('click', function() {
                resetAndHideForm();
                $('#confirmModal').modal('hide');
            });
        } else {
            // If in 'add' mode, just reset the form instantly
            resetAndHideForm();
        }
    });
    
    $('#productForm').submit(function(e) {
        e.preventDefault();
        $.post('product-actions.php', $(this).serialize(), res => {
            toastr[res.success ? 'success' : 'error'](res.message);
            if (res.success) {
                resetAndHideForm();
                table.ajax.reload();
            }
        }, 'json').fail(xhr => {
            toastr.error('Request failed. Check console.');
            console.error(xhr.responseText);
        });
    });

    // --- TABLE ACTION BUTTONS (EDIT/DELETE) ---
    $('#productTable tbody').on('click', '.btn-edit', function() {
        let row = $(this).closest('tr');
        let data = table.row(row).data();
        showFormForEdit(data);
        row.addClass('table-warning');
    });

    $('#productTable tbody').on('click', '.btn-delete', function() {
        let row = $(this).closest('tr');
        let data = table.row(row).data();
        $('#confirmModalTitle').text('Delete Product');
        $('#confirmModalBody').html(`Are you sure you want to delete <strong>${data.name}</strong>?`);
        $('#confirmModalBtn')
            .text('Delete')
            .removeClass('btn-warning') // Ensure it's not yellow
            .addClass('btn-danger');    // Make it red for delete action
        
        $('#confirmModal').modal('show');

        $('#confirmModalBtn').one('click', function() {
            $.post('product-actions.php', { action: 'delete', id: data.id }, res => {
                toastr[res.success ? 'success' : 'error'](res.message);
                if (res.success) table.ajax.reload();
            }, 'json').fail(xhr => {
                toastr.error('Request failed. Check console.');
                console.error(xhr.responseText);
            }).always(() => $('#confirmModal').modal('hide'));
        });
    });
});
</script>
</body>
</html>