<?php
session_start();

// Restrict access: Show modal and redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    echo "
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css' rel='stylesheet'>
    <div class='modal fade show' id='errorModal' tabindex='-1' aria-labelledby='errorModalLabel' aria-hidden='false' 
         style='display: block; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 9999;'>
        <div class='modal-dialog modal-dialog-centered'>
            <div class='modal-content'>
                <div class='modal-header'>
                    <h5 class='modal-title' id='errorModalLabel'>Access Denied</h5>
                </div>
                <div class='modal-body'>
                    You must be logged in to view this page.
                </div>
                <div class='modal-footer'>
                    <button type='button' class='btn btn-primary' onclick=\"window.location.href='../user/login.php'\">OK</button>
                </div>
            </div>
        </div>
    </div>
    <div class='modal-backdrop fade show' 
         style='position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); z-index: 9998;'>
    </div>
    <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js'></script>
    ";
    exit();
}
?>
