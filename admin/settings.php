// admin/settings.php (Snippet)
if (isset($_POST['upload_favicon'])) {
    $target_dir = "../assets/";
    $target_file = $target_dir . "favicon.ico";
    
    if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
        echo "Favicon updated successfully!";
    }
}