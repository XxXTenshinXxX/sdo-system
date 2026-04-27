<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

$userRole = $_SESSION['role'] ?? 'staff';
$profileInitial = strtoupper(substr(trim($userRole), 0, 1));
$roleClass = strtolower(trim($userRole)) === 'super admin' ? 'role-super-admin' : '';
$pageTitle = 'Leave List';
$activePage = 'leave-list';
?>
<?php include __DIR__ . '/includes/header.php'; ?>
<body>
    <div class="layout">
        <?php include __DIR__ . '/includes/sidebar.php'; ?>

        <main class="content">
            <?php include __DIR__ . '/includes/navbar.php'; ?>

            <div class="content-body">
                <section class="toolbar-card">
                    <div class="toolbar">
                        <div>
                            <h2>Leave List</h2>
                            <p>Manage employee leave records, import spreadsheet data, and add new employees.</p>
                        </div>

                        <div class="toolbar-actions">
                            <button type="button" class="btn btn-soft" onclick="openUploadModal()">
                                <i class="fa-solid fa-upload"></i>
                                Upload XLSX
                            </button>

                            <a href="#" class="btn btn-primary">
                                <i class="fa-solid fa-user-plus"></i>
                                Add Employee
                            </a>
                        </div>
                    </div>
                </section>

                <section class="section">
                    <div class="table-card">
                        <div class="section-header">
                            <h3>Employee Leave Records</h3>
                            <a href="#">Export Table</a>
                        </div>

                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Employee ID</th>
                                    <th>Employee Name</th>
                                    <th>Department</th>
                                    <th>Leave Type</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>EMP-001</td>
                                    <td>Maria Santos</td>
                                    <td>Human Resources</td>
                                    <td>Vacation Leave</td>
                                    <td>2026-04-24</td>
                                    <td>2026-04-26</td>
                                    <td><span class="status approved">Approved</span></td>
                                    <td>
                                        <div class="table-actions">
                                            <button type="button" class="table-btn edit">Edit</button>
                                            <button type="button" class="table-btn delete">Delete</button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>EMP-002</td>
                                    <td>Jose Rivera</td>
                                    <td>Accounting</td>
                                    <td>Sick Leave</td>
                                    <td>2026-04-23</td>
                                    <td>2026-04-23</td>
                                    <td><span class="status pending">Pending</span></td>
                                    <td>
                                        <div class="table-actions">
                                            <button type="button" class="table-btn edit">Edit</button>
                                            <button type="button" class="table-btn delete">Delete</button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>EMP-003</td>
                                    <td>Angela Cruz</td>
                                    <td>Operations</td>
                                    <td>Emergency Leave</td>
                                    <td>2026-04-22</td>
                                    <td>2026-04-22</td>
                                    <td><span class="status review">For Review</span></td>
                                    <td>
                                        <div class="table-actions">
                                            <button type="button" class="table-btn edit">Edit</button>
                                            <button type="button" class="table-btn delete">Delete</button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>EMP-004</td>
                                    <td>Daniel Flores</td>
                                    <td>ICT Unit</td>
                                    <td>Personal Leave</td>
                                    <td>2026-04-21</td>
                                    <td>2026-04-21</td>
                                    <td><span class="status approved">Approved</span></td>
                                    <td>
                                        <div class="table-actions">
                                            <button type="button" class="table-btn edit">Edit</button>
                                            <button type="button" class="table-btn delete">Delete</button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>

            <?php include __DIR__ . '/includes/footer.php'; ?>
        </main>
    </div>

    <div class="modal-overlay" id="uploadModal">
        <div class="modal-card">
            <h3>Upload XLSX File</h3>
            <p>Select an Excel file to import employee leave records into the table.</p>

            <form class="upload-form" action="#" method="POST" enctype="multipart/form-data">
                <div class="form-field">
                    <label for="uploadLeaveGroup">Select Group</label>
                    <select id="uploadLeaveGroup" name="upload_group" required>
                        <option value="">Select group</option>
                        <option value="ES">ES</option>
                        <option value="SEC">SEC</option>
                    </select>
                </div>

                <div class="form-field">
                    <label for="uploadLeaveStatus">Select Status</label>
                    <select id="uploadLeaveStatus" name="upload_status" required>
                        <option value="">Select status</option>
                        <option value="Active">Active</option>
                        <option value="Inactivation">Inactivation</option>
                        <option value="Separation">Separation</option>
                    </select>
                </div>

                <div class="file-upload-field">
                    <input class="file-input" id="leaveFileInput" type="file" name="leave_file" accept=".xlsx,.xls" required>
                    <label class="file-picker" for="leaveFileInput">
                        <span class="file-picker-icon">
                            <i class="fa-solid fa-file-excel"></i>
                        </span>
                        <span class="file-picker-copy">
                            <strong>Choose XLSX File</strong>
                            <span>Click to browse or drag and drop your Excel file here.</span>
                        </span>
                        <span class="file-picker-action">Browse</span>
                    </label>
                    <p class="file-selected-name" id="selectedFileName">No file selected</p>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn btn-outline" onclick="closeUploadModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-file-arrow-up"></i>
                        Upload File
                    </button>
                </div>
            </form>
        </div>
    </div>
    <script>
        const uploadModal = document.getElementById('uploadModal');
        const leaveFileInput = document.getElementById('leaveFileInput');
        const selectedFileName = document.getElementById('selectedFileName');

        function openUploadModal() {
            uploadModal.classList.add('show');
        }

        function closeUploadModal() {
            uploadModal.classList.remove('show');
        }

        leaveFileInput.addEventListener('change', function () {
            if (leaveFileInput.files.length > 0) {
                selectedFileName.textContent = leaveFileInput.files[0].name;
                return;
            }

            selectedFileName.textContent = 'No file selected';
        });

        uploadModal.addEventListener('click', function (event) {
            if (event.target === uploadModal) {
                closeUploadModal();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && uploadModal.classList.contains('show')) {
                closeUploadModal();
            }
        });
    </script>
</body>
</html>
