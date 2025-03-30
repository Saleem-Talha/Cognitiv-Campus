                    <form method="GET" action="" class="mb-3">
                                <input type="hidden" name="id" value="<?php echo $courseId; ?>">
                                <div class="row">
                                    <div class="col-md-4">
                                        <input type="date" name="coursework_start_date" class="form-control" value="<?php echo $courseworkStartDate; ?>" placeholder="Start Date">
                                    </div>
                                    <div class="col-md-4">
                                        <input type="date" name="coursework_end_date" class="form-control" value="<?php echo $courseworkEndDate; ?>" placeholder="End Date">
                                    </div>
                                    <div class="col-md-2">
                                        <button type="submit" class="btn btn-primary btn-sm w-100">Filter</button>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="submit" name="coursework_today" class="btn btn-outline-primary btn-sm w-100">Today</button>
                                    </div>
                                </div>
                            </form>
                            <!-- Section for Course Materials -->
                            <h2 class="mb-3">Course Materials</h2>
                            <?php if (empty($courseMaterials)) : ?>
                                <!-- Display a message if no course materials are found -->
                                <div class="card shadow-sm hover-elevate-up">
                                    <div class="card-body">
                                        <h4 class="card-title d-flex align-items-center mb-4">
                                            <i class='bx bx-book-open me-2 text-primary'></i>
                                            Course Materials
                                        </h4>
                                        <div class="alert alert-primary" role="alert">
                                                            <i class="bx bx-info-circle me-2"></i>
                                                            No materials found.
                                                        </div>
                                    </div>
                                </div>
                            <?php else : ?>
                                <!-- Display course materials with pagination -->
                                <div class="card shadow-sm hover-elevate-up">
                                    <div class="card-body">
                                        <h4 class="card-title d-flex align-items-center mb-4">
                                            <i class='bx bx-book-open me-2 text-primary'></i>
                                            Course Materials
                                        </h4>
                                        <div id="courseMaterialsList">
                                            <!-- Course materials will be dynamically inserted here -->
                                        </div>
                                        <nav aria-label="Course materials pagination" class="mt-4">
                                            <ul class="pagination justify-content-center" id="pagination">
                                                <!-- Pagination buttons will be dynamically inserted here -->
                                            </ul>
                                        </nav>
                                    </div>
                                </div>
                            <?php endif; ?>