<?php
require_once 'vendor/autoload.php';
require_once 'includes/db-connect.php';

session_start();

if (!isset($_SESSION['access_token'])) {
    header('Location: index.php');
    exit();
}

$client = new Google_Client();
$client->setAccessToken($_SESSION['access_token']);

$classroomService = new Google_Service_Classroom($client);
$driveService = new Google_Service_Drive($client);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $courseId = $_POST['course_id'];
    $courseworkId = $_POST['coursework_id'];

    if (isset($_FILES['assignment_file']) && $_FILES['assignment_file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['assignment_file'];

        try {
            // Check if the course and coursework exist
            try {
                $course = $classroomService->courses->get($courseId);
                $coursework = $classroomService->courses_courseWork->get($courseId, $courseworkId);
            } catch (Google_Service_Exception $e) {
                throw new Exception("Course or coursework not found. Please check the IDs.");
            }

            // Upload file to Google Drive
            $fileMetadata = new Google_Service_Drive_DriveFile([
                'name' => $file['name']
            ]);
            $content = file_get_contents($file['tmp_name']);
            $uploadedFile = $driveService->files->create($fileMetadata, [
                'data' => $content,
                'mimeType' => $file['type'],
                'uploadType' => 'multipart',
                'fields' => 'id'
            ]);

            // Create attachment for the submission
            $attachment = new Google_Service_Classroom_Attachment([
                'driveFile' => new Google_Service_Classroom_DriveFile([
                    'id' => $uploadedFile->getId()
                    // Remove 'title' field
                ])
            ]);

            // List student submissions to get the submission ID
            $submissions = $classroomService->courses_courseWork_studentSubmissions->listCoursesCourseWorkStudentSubmissions(
                $courseId,
                $courseworkId,
                ['userId' => 'me']
            );

            if (count($submissions->getStudentSubmissions()) > 0) {
                $submission = $submissions->getStudentSubmissions()[0];
                $submissionId = $submission->getId();
            } else {
                throw new Exception("No submission found for this coursework. Please try to open the assignment in Google Classroom first.");
            }

            // Add the attachment to the submission
            $addAttachmentRequest = new Google_Service_Classroom_ModifyAttachmentsRequest([
                'addAttachments' => [$attachment]
            ]);

            $classroomService->courses_courseWork_studentSubmissions->modifyAttachments(
                $courseId,
                $courseworkId,
                $submissionId,
                $addAttachmentRequest
            );

            // Turn in the submission
            $turnInRequest = new Google_Service_Classroom_TurnInStudentSubmissionRequest();
            $classroomService->courses_courseWork_studentSubmissions->turnIn(
                $courseId,
                $courseworkId,
                $submissionId,
                $turnInRequest
            );

            // Redirect with success message
            header("Location: subject-details-coursework.php?course_id=$courseId&work_id=$courseworkId&upload_success=1");
            exit();
        } catch (Exception $e) {
            // Redirect with error message
            $error = urlencode($e->getMessage());
            header("Location: subject-details-coursework.php?course_id=$courseId&work_id=$courseworkId&upload_error=$error");
            exit();
        }
    } else {
        // Redirect with error message for file upload issue
        $error = urlencode("File upload failed. Please try again.");
        header("Location: subject-details-coursework.php?course_id=$courseId&work_id=$courseworkId&upload_error=$error");
        exit();
    }
} else {
    // Redirect to coursework details page if accessed directly
    header("Location: subject-details-coursework.php");
    exit();
}
