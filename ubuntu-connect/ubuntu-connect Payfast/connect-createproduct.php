<?php
include 'includes/header.php';

//kick users out if there is no active session
if (!isset($_SESSION['userID'])) {
    header("Location: " . base_URL . "/connect-login.php");
    exit;
}

$errorMessage = '';

// Handle the publishing of a product
if ($_SERVER['REQUEST_METHOD'] === 'POST') 
{
    $productTitle = trim($_POST['title']);
    $productDescription = trim($_POST['description']);
    $productPrice = floatval($_POST['price']);
    $productCondition = $_POST['condition'];
    $productCategory = trim($_POST['category']);
   
     $imageName = 'placeholder.png'; //placeholder image

    // Ensures that the filetype is correct
  if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) 
{

    $filesAllowed = ['jpg', 'jpeg', 'png', 'webp'];
    $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $filesAllowed))
    {
        die("Invalid file type. Only JPG, PNG, WEBP allowed.");
    }
}

    // Set a fallback name in case the file selection block is empty
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) 
    {
        // This code is used to build a unique filename token using timestamp indicators
        $imageName = time() . '_' . $_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'], 'uploads/' . $imageName);
    }

    //Create a transactional insert query statement with parameters that targets the listings store.
    $stmt = $pdo->prepare("INSERT INTO tbllistings (sellerID, listingTitle, listingDescription, listingPrice, listingCategory, listingCondition, listingImage)
     VALUES (?, ?, ?, ?, ?, ?, ?)");

    if ($stmt->execute([$_SESSION['userID'], $productTitle, $productDescription, $productPrice, $productCategory, $productCondition, $imageName])) 
    {
        $errorMessage = "<div class='alert alert-success'>Product listed successfully!</div>";
    } 
    else 
    {
        $errorMessage = "<div class='alert alert-danger'>Failed to post listing.</div>";
    }
}
?>

<h2>List a Product for Sale</h2>
<?= $errorMessage ?>
<form action="connect-createproduct.php" method="POST" enctype="multipart/form-data" class="card p-4 shadow-sm">
    <div class="mb-3">
        <label class="form-label">Title</label>
        <input type="text" name="title" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="3" required></textarea>
    </div>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">Price (ZAR)</label>
            <input type="number" step="0.01" name="price" class="form-control" required>
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">Category</label>
            <input type="text" name="category" class="form-control" placeholder="e.g., Electronics, Books" required>
        </div>
    </div>
    <div class="mb-3">
        <label class="form-label">Condition</label>
        <select name="condition" class="form-select">
            <option value="new">New</option>
            <option value="like new">Like New</option>
            <option value="good" selected>Good</option>
            <option value="fair">Fair</option>
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label">Product Image</label>
        <input type="file" name="image" class="form-control">
    </div>
    <button type="submit" class="btn btn-success">Publish Listing</button>
</form>

<?php include 'includes/footer.php'; ?>