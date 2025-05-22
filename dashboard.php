<?php
session_start();
include 'config/db_connect.php';
include 'includes/functions.php';

checkLogin();

// Get quick statistics
$stats = [
    'patients' => $conn->query("SELECT COUNT(*) as count FROM patients")->fetch_assoc()['count'],
    'doctors' => $conn->query("SELECT COUNT(*) as count FROM doctors")->fetch_assoc()['count'],
    'appointments' => $conn->query("SELECT COUNT(*) as count FROM appointments")->fetch_assoc()['count'],
    'today_appointments' => $conn->query("SELECT COUNT(*) as count FROM appointments WHERE DATE(appointment_date) = CURDATE()")->fetch_assoc()['count']
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics Dashboard - HMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container-fluid dashboard-container">
        <!-- Loading Spinner -->
        <div id="loading-spinner" class="loading-spinner" style="display: none;"></div>

        <!-- Alert Container -->
        <div id="alert-container"></div>

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title mb-0">Total Patients</h6>
                                <h2 class="mt-2 mb-0"><?php echo $stats['patients']; ?></h2>
                            </div>
                            <div class="icon">
                                <i class="bi bi-people"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title mb-0">Total Doctors</h6>
                                <h2 class="mt-2 mb-0"><?php echo $stats['doctors']; ?></h2>
                            </div>
                            <div class="icon">
                                <i class="bi bi-person-badge"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title mb-0">Total Appointments</h6>
                                <h2 class="mt-2 mb-0"><?php echo $stats['appointments']; ?></h2>
                            </div>
                            <div class="icon">
                                <i class="bi bi-calendar-check"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title mb-0">Today's Appointments</h6>
                                <h2 class="mt-2 mb-0"><?php echo $stats['today_appointments']; ?></h2>
                            </div>
                            <div class="icon">
                                <i class="bi bi-calendar-day"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="row">
            <!-- Monthly Appointments -->
            <div class="col-md-8">
                <div class="card dashboard-card">
                    <div class="card-header bg-transparent">
                        <h5 class="mb-0">Monthly Appointments Overview</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="monthlyChart" height="300"></canvas>
                    </div>
                </div>
            </div>

            <!-- Gender Distribution -->
            <div class="col-md-4">
                <div class="card dashboard-card">
                    <div class="card-header bg-transparent">
                        <h5 class="mb-0">Patient Gender Distribution</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="genderChart" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <!-- Department Statistics -->
            <div class="col-md-6">
                <div class="card dashboard-card">
                    <div class="card-header bg-transparent">
                        <h5 class="mb-0">Department Statistics</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="departmentChart" height="300"></canvas>
                    </div>
                </div>
            </div>

            <!-- Daily Distribution -->
            <div class="col-md-6">
                <div class="card dashboard-card">
                    <div class="card-header bg-transparent">
                        <h5 class="mb-0">Daily Appointment Distribution</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="dailyChart" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
    <script>
        // Fetch and display statistics
        async function loadStatistics() {
            try {
                showLoading();
                const response = await fetch('api/statistics.php');
                const data = await response.json();
                
                if (data.error) {
                    showError(data.error);
                    return;
                }

                // Monthly Appointments Chart
                new Chart(document.getElementById('monthlyChart'), {
                    type: 'line',
                    data: {
                        labels: data.monthly_stats.map(item => item.month),
                        datasets: [{
                            label: 'Total',
                            data: data.monthly_stats.map(item => item.total),
                            borderColor: '#0d6efd',
                            tension: 0.1
                        }, {
                            label: 'Completed',
                            data: data.monthly_stats.map(item => item.completed),
                            borderColor: '#198754',
                            tension: 0.1
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            title: {
                                display: true,
                                text: 'Monthly Appointments Trend'
                            }
                        }
                    }
                });

                // Gender Distribution Chart
                new Chart(document.getElementById('genderChart'), {
                    type: 'doughnut',
                    data: {
                        labels: data.gender_distribution.map(item => item.gender),
                        datasets: [{
                            data: data.gender_distribution.map(item => item.count),
                            backgroundColor: ['#0d6efd', '#dc3545', '#ffc107']
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            title: {
                                display: true,
                                text: 'Patient Gender Distribution'
                            }
                        }
                    }
                });

                // Department Statistics Chart
                new Chart(document.getElementById('departmentChart'), {
                    type: 'bar',
                    data: {
                        labels: data.department_stats.map(item => item.specialization),
                        datasets: [{
                            label: 'Doctors',
                            data: data.department_stats.map(item => item.doctor_count),
                            backgroundColor: '#0d6efd'
                        }, {
                            label: 'Appointments',
                            data: data.department_stats.map(item => item.appointment_count),
                            backgroundColor: '#198754'
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            title: {
                                display: true,
                                text: 'Department Statistics'
                            }
                        }
                    }
                });

                // Daily Distribution Chart
                new Chart(document.getElementById('dailyChart'), {
                    type: 'bar',
                    data: {
                        labels: data.daily_distribution.map(item => item.day_name),
                        datasets: [{
                            label: 'Appointments',
                            data: data.daily_distribution.map(item => item.appointment_count),
                            backgroundColor: '#0d6efd'
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            title: {
                                display: true,
                                text: 'Daily Appointment Distribution'
                            }
                        }
                    }
                });
            } catch (error) {
                showError('Error loading statistics');
            } finally {
                hideLoading();
            }
        }

        // Load statistics when page loads
        document.addEventListener('DOMContentLoaded', loadStatistics);
    </script>
</body>
</html>
