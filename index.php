<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factory Work & Billing System</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'nav.php'; ?>
    <div class="container">
        <div class="stats-grid">
            <div class="card stat-card" style="border-left: 8px solid var(--warning);">
                <h3><i class="fa-solid fa-clock"></i> Pending Payment</h3>
                <p class="stat-value" id="pendingAmount" style="color: var(--warning);">₹0.00</p>
            </div>
            <div class="card stat-card" style="border-left: 8px solid var(--success);">
                <h3><i class="fa-solid fa-check-circle"></i> Completed Jobs</h3>
                <p class="stat-value" id="completedJobs" style="color: var(--success);">0</p>
            </div>
        </div>

        <div class="content-grid">
            <div class="card form-card">
                <h2 style="color: var(--primary);"><i class="fa-solid fa-plus-circle"></i> Add New Work</h2>
                <form id="addWorkForm">
                    <div class="form-group">
                        <label><i class="fa-solid fa-user"></i> Customer Name</label>
                        <input type="text" name="customer_name" id="customer_name" list="customerList" placeholder="Who is this for?">
                        <datalist id="customerList"></datalist>
                    </div>
                    <div class="form-group">
                        <label><i class="fa-solid fa-briefcase"></i> Job Name</label>
                        <input type="text" name="job_name" id="job_name" list="jobList" required placeholder="E.g., Mold #1">
                        <datalist id="jobList"></datalist>
                    </div>
                    <div class="form-group">
                        <label><i class="fa-solid fa-layer-group"></i> Category</label>
                        <select name="category" id="categorySelect">
                            <option value="VMC">VMC</option>
                            <option value="CNC">CNC</option>
                            <option value="Drill">Drill</option>
                            <option value="Lathe">Lathe</option>
                            <option value="Inspection">Inspection</option>
                            <option value="Fitting">Fitting</option>
                            <option value="Martial">Martial (Material)</option>
                            <option value="Parts">Parts</option>
                            <option value="Grinding">Grinding</option>
                            <option value="Labor">Labor</option>
                            <option value="Other">Other</option>
                        </select>
                        <input type="text" id="customCategoryInput" placeholder="Enter custom category" style="display: none; margin-top: 0.5rem; width: 100%; padding: 0.8rem; border: 2px solid var(--border); border-radius: 8px;">
                        
                        <!-- Drill Options -->
                        <div id="drillOptions" style="display: none; margin-top: 0.5rem; padding: 1rem; background: #f8fafc; border: 2px solid #e2e8f0; border-radius: 8px;">
                            <div style="display: flex; gap: 15px; margin-bottom: 5px;">
                                <div style="flex: 1;">
                                    <label>Type</label>
                                    <select id="drillType">
                                        <option value="10">Hole (₹10)</option>
                                        <option value="20">Rimer (₹20)</option>
                                        <option value="50">Hole Thread (₹50)</option>
                                        <option value="90">Miling (₹90)</option>
                                    </select>
                                </div>
                                <div style="flex: 1;">
                                     <label>Count</label>
                                     <input type="number" id="drillCount" value="1" min="1">
                                </div>
                            </div>
                        </div>

                        <!-- Lathe Options -->
                        <div id="latheOptions" style="display: none; margin-top: 0.5rem; padding: 1rem; background: #f8fafc; border: 2px solid #e2e8f0; border-radius: 8px;">
                            <label>Operation</label>
                            <select id="latheOp">
                                <option value="Precision turning">Precision Turning</option>
                                <option value="Normal turning">Normal Turning</option>
                                <option value="Thread">Thread</option>
                            </select>
                        </div>

                        <!-- Parts Options -->
                        <div id="partsOptions" style="display: none; margin-top: 0.5rem; padding: 1rem; background: #f8fafc; border: 2px solid #e2e8f0; border-radius: 8px;">
                            <div style="margin-bottom: 10px;">
                                <label>Part Type</label>
                                <select id="partType">
                                    <option value="Bolts">Bolts</option>
                                    <option value="Pins">Pins</option>
                                    <option value="Pin Buss">Pin Buss</option>
                                </select>
                            </div>
                            <div id="boltSizeDiv" style="margin-bottom: 10px;">
                                <label>Size (Ani)</label>
                                <select id="boltSize">
                                    <?php for($i=1; $i<=15; $i++) echo "<option value='$i'>$i Ani</option>"; ?>
                                </select>
                            </div>
                            <div>
                                <label>Count</label>
                                <input type="number" id="partCount" value="1" min="1">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label><i class="fa-solid fa-align-left"></i> Description</label>
                        <input type="text" name="description" placeholder="Any extra details (Optional)">
                    </div>
                    <div class="form-group">
                        <label><i class="fa-solid fa-calendar"></i> Date</label>
                        <div style="display: flex; gap: 10px;">
                            <input type="date" name="work_date" id="workDateInput" required>
                            <button type="button" class="btn btn-secondary btn-sm" id="setDateBtn" style="white-space: nowrap;"><i class="fa-solid fa-calendar-day"></i> Today</button>
                        </div>
                    </div>
                    <div class="form-group">
                        <label><i class="fa-solid fa-indian-rupee-sign"></i> Amount (₹)</label>
                        <input type="number" step="0.01" name="amount" id="amount" required placeholder="0.00" style="font-size: 1.5rem; font-weight: bold; color: var(--primary);">
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%; font-size: 1.2rem; padding: 1rem;"><i class="fa-solid fa-check"></i> Add Work Now</button>
                </form>
            </div>

            <div class="card list-card">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 1rem;">
                    <h2 style="margin: 0;"><i class="fa-solid fa-list"></i> Recent Works</h2>
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <select id="sortWorksBy" style="padding: 0.5rem; border: 1px solid var(--border); border-radius: 8px; font-size: 0.9rem;">
                            <option value="date_desc">Date (Newest)</option>
                            <option value="date_asc">Date (Oldest)</option>
                            <option value="amount_desc">Amount (Highest)</option>
                            <option value="amount_asc">Amount (Lowest)</option>
                        </select>
                        <button id="toggleScrollBtn" class="btn btn-sm btn-secondary" style="white-space: nowrap;"><i class="fa-solid fa-arrows-up-down"></i> Scroll</button>
                    </div>
                </div>
                <div class="table-responsive scrollable-table" id="worksTableContainer">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Details</th>
                                <th>Amount</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="workTableBody">
                            <!-- Items will be populated here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>
