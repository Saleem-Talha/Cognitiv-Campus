
<nav
            class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
            id="layout-navbar">
            <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
              <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
                <i class="bx bx-menu bx-sm"></i>
              </a>
            </div>

            <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
              <!-- Search -->
              <div class="navbar-nav align-items-center">
                <div class="nav-item d-flex align-items-center">
                  <i class="bx bx-search fs-4 lh-0"></i>
                  <form action="search.php" method="post">
                  <input
                    type="text"
                    name="search"
                    class="form-control border-0 shadow-none ps-1 ps-sm-2"
                    placeholder="Search..."
                    aria-label="Search..." />
                    </form>
                </div>
              </div>
              <!-- /Search -->

              <ul class="navbar-nav flex-row align-items-center ms-auto">
         
              <!-- Notifications -->
              <li class="nav-item navbar-dropdown dropdown-notifications dropdown">
                <a class="nav-link dropdown-toggle" href="javascript:void(0);" role="button" data-bs-toggle="dropdown">
                  <i class="bx bx-bell"></i>
                  <?php
                  $notifications = $db->query("SELECT * FROM notice WHERE userEmail = '$userEmail' AND is_read = 'no' ORDER BY id DESC");
                  $notificationCount = $notifications->num_rows;
                  ?>
                  <span class="badge bg-danger rounded-pill"><?php echo $notificationCount; ?></span>
                </a>
                <div class="dropdown-menu dropdown-menu-end">
                  <div class="dropdown-header d-flex justify-content-between align-items-center">
                    <span>Notifications</span>
                  </div>
                  <div class="dropdown-body">
                    <?php
                    if($notificationCount > 0){
                      while($row = $notifications->fetch_assoc()){
                        $type = $row['type'];
                        $message = $row['message'];
                        $datetime = $row['datetime'];
                    ?>
                    <a href="notifications.php" class="dropdown-item">
                      <div class="d-flex">
                        <div class="flex-grow-1 ms-3">
                          <strong><?php echo $type; ?></strong> <?php echo $message; ?>
                          <div class="small text-muted"><?php echo $datetime; ?></div>
                        </div>
                      </div>
                    </a>
                    <?php
                      }
                    } else {
                    ?>
                    <a href="javascript:void(0)" class="dropdown-item">
                      <div class="d-flex">
                        <div class="flex-grow-1 ms-3">
                          No new notifications
                        </div>
                      </div>
                    </a>
                    <?php
                    }
                    ?>
                  </div>
                  <div class="dropdown-footer">
                    <a href="notifications.php" class="small ms-3">View all notifications</a>
                  </div>
                </div>
              </li>

                <!-- User -->
                <li class="nav-item navbar-dropdown dropdown-user dropdown">
                  <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                    <div class="avatar avatar-online">
                      <img src="<?php echo $userProfile; ?>" alt class="w-px-40 h-40 rounded-circle" />
                    </div>
                  </a>
                  <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                      <a class="dropdown-item" href="#">
                        <div class="d-flex">
                          <div class="flex-shrink-0 me-3">
                            <div class="avatar avatar-online">
                              <img src="<?php echo $userProfile; ?>" alt class="w-px-60 h-60 rounded-circle" />
                            </div>
                          </div>
                          <div class="flex-grow-1">
                  <span class="fw-medium d-block"><?php echo $userName ?></span>
                  <?php if ($userEmail == 'saleemtalha967@gmail.com'): ?>
                    <a href="admin-login.php" class="text-muted small">Admin</a>
                  <?php else: ?>
                    <small class="text-muted">User</small>
                  <?php endif; ?>
                </div>
                        </div>
                      </a>
                    </li>
                    <li>
                      <div class="dropdown-divider"></div>
                    </li>
                    <li>
                      <a class="dropdown-item" href="settings-account.php">
                        <i class="bx bx-user me-2"></i>
                        <span class="align-middle">My Profile</span>
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="settings-security.php">
                        <i class="bx bx-cog me-2"></i>
                        <span class="align-middle">Settings</span>
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="billing.php">
                        <span class="d-flex align-items-center align-middle">
                          <i class="flex-shrink-0 bx bx-credit-card me-2"></i>
                          <span class="flex-grow-1 align-middle ms-1">Billing</span>
                        </span>
                      </a>
                    </li>
                    <li>
                      <div class="dropdown-divider"></div>
                    </li>
                    <li>
                      <a class="dropdown-item" href="logout.php">
                        <i class="bx bx-power-off me-2"></i>
                        <span class="align-middle">Log Out</span>
                      </a>
                    </li>
                  </ul>
                </li>
              </ul>
            </div>
          </nav>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>