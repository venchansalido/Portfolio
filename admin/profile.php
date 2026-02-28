<?php
session_start();
include '../includes/config.php';
include '../includes/restriction_admin.php';
include '../includes/session_utils.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin' || !checkSessionExpiration()) {
    session_unset();
    session_destroy();
    header('Location: ../index.php');
    exit;
}

$userId = $_SESSION['user_id'] ?? 1;
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName=$_POST['first_name']; $middleName=$_POST['middle_name']; $lastName=$_POST['last_name'];
    $caption=$_POST['caption']; $bio=$_POST['bio']; $email=$_POST['email'];
    $phone=$_POST['phone_number']; $address=$_POST['street_address']; $city=$_POST['city'];
    $birthDate=$_POST['birth_date']; $age=$_POST['age']; $birthPlace=$_POST['birth_place'];
    $nationality=$_POST['nationality']; $religion=$_POST['religion'];

    $uq = "UPDATE users SET first_name=?,middle_name=?,last_name=?,caption=?,bio=?,email=?,phone_number=?,street_address=?,city=?,birth_date=?,age=?,birth_place=?,nationality=?,religion=? WHERE id=?";
    $s = $conn->prepare($uq);
    $s->bind_param("ssssssssssssssi",$firstName,$middleName,$lastName,$caption,$bio,$email,$phone,$address,$city,$birthDate,$age,$birthPlace,$nationality,$religion,$userId);

    if ($s->execute()) {
        $success = "Profile updated successfully!";
        if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error']==0) {
            $p = "../assets/images/profiles/".basename($_FILES['profile_photo']['name']);
            if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $p)) {
                $up=$conn->prepare("UPDATE users SET profile_photo=? WHERE id=?"); $up->bind_param("si",$p,$userId); $up->execute();
                $success .= " Profile photo updated!";
            }
        }
        if (isset($_FILES['gallery_images'])) {
            foreach ($_FILES['gallery_images']['tmp_name'] as $k=>$tmp) {
                if ($_FILES['gallery_images']['error'][$k]==0) {
                    $gp="../assets/images/gallery/".basename($_FILES['gallery_images']['name'][$k]);
                    if (move_uploaded_file($tmp,$gp)) {
                        $ins=$conn->prepare("INSERT INTO user_gallery (user_id,image_path) VALUES (?,?)"); $ins->bind_param("is",$userId,$gp); $ins->execute();
                    }
                }
            }
            $success .= " Gallery images uploaded!";
        }
    } else { $error = "Error: ".$conn->error; }
    $s2=$conn->prepare("SELECT * FROM users WHERE id=?"); $s2->bind_param("i",$userId); $s2->execute(); $user=$s2->get_result()->fetch_assoc();
}

if (isset($_GET['delete_image_id'])) {
    $iid=(int)$_GET['delete_image_id'];
    $q=$conn->prepare("SELECT image_path FROM user_gallery WHERE id=?"); $q->bind_param("i",$iid); $q->execute(); $img=$q->get_result()->fetch_assoc();
    if ($img && file_exists($img['image_path'])) unlink($img['image_path']);
    $d=$conn->prepare("DELETE FROM user_gallery WHERE id=?"); $d->bind_param("i",$iid); $d->execute();
    $success="Image deleted!";
}

if (isset($_FILES['cover_photo']) && $_FILES['cover_photo']['error']==0) {
    $cp="../assets/images/covers/".basename($_FILES['cover_photo']['name']);
    if (move_uploaded_file($_FILES['cover_photo']['tmp_name'],$cp)) {
        $uc=$conn->prepare("UPDATE users SET cover_photo=? WHERE id=?"); $uc->bind_param("si",$cp,$userId); $uc->execute();
        $success=($success??'')." Cover photo updated!";
    }
}

if (isset($_FILES['resume']) && $_FILES['resume']['error']==0) {
    $rp="../assets/resume/".basename($_FILES['resume']['name']);
    if (move_uploaded_file($_FILES['resume']['tmp_name'],$rp)) {
        $ur=$conn->prepare("UPDATE users SET resume_path=? WHERE id=?"); $ur->bind_param("si",$rp,$userId); $ur->execute();
        $success=($success??'')." Resume updated!";
    }
}

if (isset($_GET['delete_resume'])) {
    if (!empty($user['resume_path']) && file_exists($user['resume_path'])) unlink($user['resume_path']);
    $dr=$conn->prepare("UPDATE users SET resume_path=NULL WHERE id=?"); $dr->bind_param("i",$userId); $dr->execute();
    header("Location: edit_profile.php"); exit();
}

$gs=$conn->prepare("SELECT * FROM user_gallery WHERE user_id=?"); $gs->bind_param("i",$userId); $gs->execute();
$galleryImages=$gs->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --red:#da0416; --red-dark:#a30512; --navy:#0e2431; --navy-deep:#0e0f31;
            --body-bg:#e5ecfb; --white:#ffffff; --border:#e8e8e8;
            --muted:#6b7a8d; --input-bg:#f4f6fb; --sidebar-w:25rem;
        }
        html { font-size: 62.5%; }
        body { background:var(--body-bg); font-family:'Poppins',sans-serif; font-size:1.4rem; color:var(--navy); margin:0; }
        .wrapper { display:flex; min-height:100vh; }
        .main-content { margin-left:var(--sidebar-w); flex:1; padding:3rem 3.2rem 5rem; overflow-y:auto; }

        .page-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:3rem; padding-bottom:1.6rem; border-bottom:0.2rem solid var(--border); flex-wrap:wrap; gap:1.2rem; }
        .page-header h2 { font-size:2.4rem; font-weight:800; color:var(--navy); display:flex; align-items:center; gap:1rem; margin:0; }
        .page-header h2 i { color:var(--red); }

        .form-card { background:var(--white); border:0.1rem solid var(--border); border-radius:1.2rem; padding:2.4rem; margin-bottom:2.4rem; box-shadow:0 0.2rem 1rem rgba(14,36,49,0.06); }
        .form-card-title { font-size:1.6rem; font-weight:700; color:var(--navy); margin-bottom:2rem; display:flex; align-items:center; gap:0.8rem; padding-bottom:1.2rem; border-bottom:0.15rem solid var(--border); }
        .form-card-title i { width:3.2rem; height:3.2rem; background:linear-gradient(135deg,var(--red),var(--red-dark)); color:#fff; border-radius:0.7rem; display:inline-flex; align-items:center; justify-content:center; font-size:1.4rem; flex-shrink:0; box-shadow:0 0.3rem 0.8rem rgba(218,4,22,0.25); }

        .form-label { font-size:1.2rem; font-weight:700; color:var(--navy); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:0.5rem; display:block; }
        .form-control { background:var(--input-bg); color:var(--navy); border:0.15rem solid var(--border); border-radius:0.8rem; padding:1rem 1.4rem; font-size:1.4rem; font-family:'Poppins',sans-serif; width:100%; transition:border-color 0.2s,box-shadow 0.2s; }
        .form-control:focus { background:var(--white); border-color:var(--red); box-shadow:0 0 0 0.3rem rgba(218,4,22,0.1); outline:none; color:var(--navy); }
        .form-control::placeholder { color:var(--muted); }
        textarea.form-control { resize:vertical; min-height:12rem; }
        .form-text { font-size:1.15rem; color:var(--muted); margin-top:0.4rem; }

        .sub-heading { font-size:1.35rem; font-weight:700; color:var(--muted); text-transform:uppercase; letter-spacing:0.1em; margin:2.4rem 0 1.4rem; display:flex; align-items:center; gap:0.6rem; }
        .sub-heading::after { content:''; flex:1; height:0.1rem; background:var(--border); }

        .upload-zone { border:0.2rem dashed var(--border); border-radius:1rem; padding:2.2rem 2rem; text-align:center; cursor:pointer; transition:border-color 0.2s,background 0.2s; position:relative; }
        .upload-zone:hover { border-color:var(--red); background:rgba(218,4,22,0.03); }
        .upload-zone input[type="file"] { position:absolute; inset:0; opacity:0; cursor:pointer; width:100%; height:100%; }
        .upload-zone i { font-size:2.8rem; color:var(--red); margin-bottom:0.8rem; display:block; }
        .upload-zone p { font-size:1.3rem; color:var(--muted); margin:0; line-height:1.6; }
        .upload-zone strong { color:var(--navy); }

        .photo-preview { display:flex; align-items:center; gap:1.6rem; margin-top:1.2rem; padding:1.2rem; background:var(--input-bg); border-radius:0.8rem; border:0.1rem solid var(--border); flex-wrap:wrap; }
        .photo-preview img { width:7rem; height:7rem; object-fit:cover; border-radius:0.8rem; box-shadow:0 0.2rem 0.8rem rgba(14,36,49,0.1); flex-shrink:0; }
        .photo-preview img.rounded-circle { border-radius:50%; }
        .photo-preview-label { font-size:1.25rem; color:var(--muted); }
        .photo-preview-label strong { display:block; color:var(--navy); font-size:1.3rem; margin-bottom:0.2rem; }

        .gallery-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(13rem,1fr)); gap:1.2rem; margin-top:1.2rem; }
        .gallery-item { position:relative; border-radius:0.8rem; overflow:hidden; box-shadow:0 0.2rem 0.8rem rgba(14,36,49,0.08); aspect-ratio:1; }
        .gallery-item img { width:100%; height:100%; object-fit:cover; transition:transform 0.3s; }
        .gallery-item:hover img { transform:scale(1.06); }
        .gallery-item-delete { position:absolute; top:0.6rem; right:0.6rem; width:2.8rem; height:2.8rem; background:rgba(218,4,22,0.9); color:#fff; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:1.2rem; text-decoration:none; opacity:0; transition:opacity 0.2s; }
        .gallery-item:hover .gallery-item-delete { opacity:1; }

        .alert { padding:1.4rem 1.8rem; border-radius:0.8rem; font-size:1.4rem; font-weight:600; margin-bottom:2rem; display:flex; align-items:center; gap:1rem; border:none; }
        .alert-success { background:rgba(34,197,94,0.12); color:#166534; border-left:0.4rem solid #22c55e; }
        .alert-danger { background:rgba(218,4,22,0.08); color:var(--red-dark); border-left:0.4rem solid var(--red); }

        .btn-save { display:inline-flex; align-items:center; gap:0.8rem; background:linear-gradient(135deg,var(--red),var(--red-dark)); color:#fff; font-size:1.5rem; font-weight:700; padding:1.1rem 2.8rem; border-radius:0.8rem; border:none; cursor:pointer; font-family:'Poppins',sans-serif; box-shadow:0 0.4rem 1.4rem rgba(218,4,22,0.3); transition:transform 0.2s,box-shadow 0.2s; text-decoration:none; }
        .btn-save:hover { transform:translateY(-0.2rem); box-shadow:0 0.8rem 2rem rgba(218,4,22,0.45); color:#fff; }

        .btn-outline-navy { display:inline-flex; align-items:center; gap:0.7rem; background:transparent; color:var(--navy); font-size:1.35rem; font-weight:600; padding:0.8rem 1.6rem; border-radius:0.7rem; border:0.15rem solid var(--navy); cursor:pointer; font-family:'Poppins',sans-serif; text-decoration:none; transition:background 0.2s,color 0.2s; }
        .btn-outline-navy:hover { background:var(--navy); color:#fff; }

        .btn-outline-danger { display:inline-flex; align-items:center; gap:0.7rem; background:transparent; color:var(--red); font-size:1.35rem; font-weight:600; padding:0.8rem 1.6rem; border-radius:0.7rem; border:0.15rem solid var(--red); cursor:pointer; font-family:'Poppins',sans-serif; text-decoration:none; transition:background 0.2s,color 0.2s; }
        .btn-outline-danger:hover { background:var(--red); color:#fff; }

        .toggle-btn { display:none; position:fixed; top:1.4rem; left:1.4rem; font-size:2.2rem; background:none; border:none; color:var(--navy); cursor:pointer; z-index:1100; transition:color 0.2s; }
        .toggle-btn:hover { color:var(--red); }

        .modal-content { border-radius:1.2rem; border:none; box-shadow:0 1.2rem 4rem rgba(14,36,49,0.18); }
        .modal-header { border-bottom:0.1rem solid var(--border); padding:1.8rem 2.4rem; }
        .modal-title { font-size:1.7rem; font-weight:700; color:var(--navy); }
        .modal-body { font-size:1.4rem; color:var(--muted); padding:2rem 2.4rem; }
        .modal-footer { border-top:0.1rem solid var(--border); padding:1.4rem 2.4rem; }

        @media (max-width:768px) {
            html { font-size:56%; }
            .main-content { margin-left:0; padding:6rem 1.6rem 4rem; }
            .toggle-btn { display:block; }
        }
    </style>
</head>
<body>
    <button class="toggle-btn" id="menuToggle"><i class="fas fa-bars"></i></button>
    <div class="wrapper">
        <?php include '../includes/sidebar.php'; ?>
        <div class="main-content">

            <div class="page-header">
                <h2><i class="fas fa-user-pen"></i> Edit Profile</h2>
                <a href="profile.php" class="btn-outline-navy"><i class="fas fa-arrow-left"></i> Back to Profile</a>
            </div>

            <?php if (isset($success)): ?>
                <div class="alert alert-success"><i class="fas fa-circle-check"></i> <?= $success ?></div>
            <?php endif; ?>
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><i class="fas fa-circle-exclamation"></i> <?= $error ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="row g-4">

                    <!-- LEFT -->
                    <div class="col-lg-6">

                        <div class="form-card">
                            <div class="form-card-title"><i class="fas fa-user"></i> Basic Information</div>
                            <div class="mb-3">
                                <label class="form-label">First Name</label>
                                <input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($user['first_name']??'') ?>" placeholder="e.g. Juan">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Middle Name</label>
                                <input type="text" name="middle_name" class="form-control" value="<?= htmlspecialchars($user['middle_name']??'') ?>" placeholder="e.g. Dela">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Last Name</label>
                                <input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars($user['last_name']??'') ?>" placeholder="e.g. Cruz">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Caption</label>
                                <input type="text" name="caption" class="form-control" value="<?= htmlspecialchars($user['caption']??'') ?>" placeholder="e.g. Full-Stack Developer">
                            </div>
                            <div class="mb-0">
                                <label class="form-label">Bio</label>
                                <textarea name="bio" class="form-control" placeholder="Write a short bio..."><?= htmlspecialchars($user['bio']??'') ?></textarea>
                            </div>
                        </div>

                        <div class="form-card">
                            <div class="form-card-title"><i class="fas fa-images"></i> Photos</div>

                            <label class="form-label">Profile Photo</label>
                            <div class="upload-zone">
                                <input type="file" name="profile_photo" accept="image/*" id="profileInput">
                                <i class="fas fa-user-circle"></i>
                                <p><strong>Click to upload</strong> or drag &amp; drop</p>
                                <p>PNG, JPG, WEBP accepted</p>
                            </div>
                            <?php if (!empty($user['profile_photo'])): ?>
                            <div class="photo-preview">
                                <img src="<?= htmlspecialchars($user['profile_photo']) ?>" class="rounded-circle" alt="Profile">
                                <div class="photo-preview-label"><strong>Current Profile Photo</strong>Upload a new file to replace</div>
                            </div>
                            <?php endif; ?>

                            <div class="sub-heading">Cover Photo</div>
                            <div class="upload-zone">
                                <input type="file" name="cover_photo" accept="image/*" id="coverInput">
                                <i class="fas fa-panorama"></i>
                                <p><strong>Click to upload</strong> or drag &amp; drop</p>
                                <p>Recommended: 1200 &times; 400px</p>
                            </div>
                            <?php if (!empty($user['cover_photo'])): ?>
                            <div class="mt-2">
                                <img src="<?= htmlspecialchars($user['cover_photo']) ?>" style="width:100%;height:9rem;border-radius:0.8rem;object-fit:cover;" alt="Cover">
                            </div>
                            <?php endif; ?>
                        </div>

                    </div>

                    <!-- RIGHT -->
                    <div class="col-lg-6">

                        <div class="form-card">
                            <div class="form-card-title"><i class="fas fa-address-book"></i> Contact Information</div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']??'') ?>" placeholder="you@email.com">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Phone Number</label>
                                <input type="text" name="phone_number" class="form-control" value="<?= htmlspecialchars($user['phone_number']??'') ?>" placeholder="+63 912 345 6789">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Street Address</label>
                                <input type="text" name="street_address" class="form-control" value="<?= htmlspecialchars($user['street_address']??'') ?>">
                            </div>
                            <div class="mb-0">
                                <label class="form-label">City</label>
                                <input type="text" name="city" class="form-control" value="<?= htmlspecialchars($user['city']??'') ?>">
                            </div>
                        </div>

                        <div class="form-card">
                            <div class="form-card-title"><i class="fas fa-id-card"></i> Personal Information</div>
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <label class="form-label">Birth Date</label>
                                    <input type="date" name="birth_date" class="form-control" value="<?= htmlspecialchars($user['birth_date']??'') ?>">
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label">Age</label>
                                    <input type="number" name="age" class="form-control" value="<?= htmlspecialchars($user['age']??'') ?>" placeholder="22">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Place of Birth</label>
                                    <input type="text" name="birth_place" class="form-control" value="<?= htmlspecialchars($user['birth_place']??'') ?>">
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label">Nationality</label>
                                    <input type="text" name="nationality" class="form-control" value="<?= htmlspecialchars($user['nationality']??'') ?>">
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label">Religion</label>
                                    <input type="text" name="religion" class="form-control" value="<?= htmlspecialchars($user['religion']??'') ?>">
                                </div>
                            </div>
                        </div>

                        <div class="form-card">
                            <div class="form-card-title"><i class="fas fa-file-pdf"></i> Resume</div>
                            <div class="upload-zone">
                                <input type="file" name="resume" accept=".pdf,.doc,.docx">
                                <i class="fas fa-file-arrow-up"></i>
                                <p><strong>Click to upload</strong> resume</p>
                                <p>PDF, DOC, DOCX accepted</p>
                            </div>
                            <?php if (!empty($user['resume_path'])): ?>
                            <div class="photo-preview mt-2">
                                <div style="width:4.8rem;height:4.8rem;background:linear-gradient(135deg,var(--red),var(--red-dark));border-radius:0.8rem;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                    <i class="fas fa-file-pdf" style="color:#fff;font-size:2rem;"></i>
                                </div>
                                <div class="photo-preview-label" style="flex:1;"><strong>Resume on file</strong>Upload a new file to replace</div>
                                <div class="d-flex gap-2 flex-wrap">
                                    <a href="<?= htmlspecialchars($user['resume_path']) ?>" target="_blank" class="btn-outline-navy"><i class="fas fa-eye"></i> View</a>
                                    <button type="button" class="btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteResumeModal"><i class="fas fa-trash"></i> Remove</button>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>

                    </div>
                </div>

                <!-- Gallery -->
                <div class="form-card">
                    <div class="form-card-title"><i class="fas fa-photo-film"></i> Gallery Images</div>
                    <label class="form-label">Upload New Images</label>
                    <div class="upload-zone">
                        <input type="file" name="gallery_images[]" accept="image/*" multiple>
                        <i class="fas fa-cloud-arrow-up"></i>
                        <p><strong>Click to upload</strong> multiple images</p>
                        <p>These appear in your About section gallery</p>
                    </div>
                    <?php if (!empty($galleryImages)): ?>
                        <div class="sub-heading mt-3">Current Gallery</div>
                        <div class="gallery-grid">
                            <?php foreach ($galleryImages as $image): ?>
                            <div class="gallery-item">
                                <img src="<?= htmlspecialchars($image['image_path']) ?>" alt="Gallery">
                                <a href="?delete_image_id=<?= $image['id'] ?>" class="gallery-item-delete" title="Delete" onclick="return confirm('Delete this image?')">
                                    <i class="fas fa-xmark"></i>
                                </a>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="form-text mt-2">No gallery images yet. Upload some above!</p>
                    <?php endif; ?>
                </div>

                <div class="d-flex justify-content-end gap-3 mt-2">
                    <a href="profile.php" class="btn-outline-navy"><i class="fas fa-xmark"></i> Cancel</a>
                    <button type="submit" class="btn-save"><i class="fas fa-floppy-disk"></i> Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Resume Modal -->
    <div class="modal fade" id="deleteResumeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-triangle-exclamation" style="color:var(--red);margin-right:0.6rem;"></i> Delete Resume</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">Are you sure you want to remove your resume? This cannot be undone.</div>
                <div class="modal-footer">
                    <button type="button" class="btn-outline-navy" data-bs-dismiss="modal">Cancel</button>
                    <a href="?delete_resume=true" class="btn-outline-danger"><i class="fas fa-trash"></i> Delete Resume</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const menuToggle = document.getElementById('menuToggle');
        const sidebar    = document.getElementById('sidebar');
        if (menuToggle && sidebar) menuToggle.addEventListener('click', () => sidebar.classList.toggle('active'));

        document.querySelectorAll('.upload-zone input[type="file"]').forEach(input => {
            input.addEventListener('change', function () {
                const label = this.closest('.upload-zone').querySelector('p strong');
                if (label && this.files.length) {
                    label.textContent = this.files.length > 1 ? this.files.length + ' files selected' : this.files[0].name;
                }
            });
        });
    </script>
</body>
</html>