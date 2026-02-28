<?php
session_start();
include '../includes/config.php';
include '../includes/restriction_admin.php';
include '../includes/session_utils.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin' || !checkSessionExpiration()) {
    session_unset(); session_destroy(); header('Location: ../index.php'); exit;
}

$query = "SELECT * FROM home ORDER BY id DESC LIMIT 1";
$result = $conn->query($query);
$home = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $greetings=$_POST['greetings']; $facebook=$_POST['facebook_link'];
    $instagram=$_POST['instagram_link']; $youtube=$_POST['youtube_link']; $typing_text=$_POST['typing_text'];
    if ($home) {
        $u=$conn->prepare("UPDATE home SET greetings=?,facebook_link=?,instagram_link=?,youtube_link=?,typing_text=? WHERE id=?");
        $u->bind_param("sssssi",$greetings,$facebook,$instagram,$youtube,$typing_text,$home['id']); $u->execute();
        $success="Homepage content updated successfully!";
    } else {
        $i=$conn->prepare("INSERT INTO home (greetings,facebook_link,instagram_link,youtube_link,typing_text) VALUES (?,?,?,?,?)");
        $i->bind_param("sssss",$greetings,$facebook,$instagram,$youtube,$typing_text); $i->execute();
        $success="Homepage content created!";
    }
    $home=$conn->query("SELECT * FROM home ORDER BY id DESC LIMIT 1")->fetch_assoc();
    if (isset($_FILES['hero_images']) && count($_FILES['hero_images']['name'])>0) {
        for ($i=0;$i<count($_FILES['hero_images']['name']);$i++) {
            if ($_FILES['hero_images']['error'][$i]==0) {
                $p="../assets/images/".basename($_FILES['hero_images']['name'][$i]);
                if (move_uploaded_file($_FILES['hero_images']['tmp_name'][$i],$p)) {
                    $s=$conn->prepare("INSERT INTO home_images (image_path) VALUES (?)"); $s->bind_param("s",$p); $s->execute();
                }
            }
        }
        $success.=" Images uploaded!";
    }
}

if (isset($_GET['delete_image_id'])) {
    $imageId=$_GET['delete_image_id'];
    $q=$conn->prepare("SELECT image_path FROM home_images WHERE id=?"); $q->bind_param("i",$imageId); $q->execute(); $q->bind_result($imagePath); $q->fetch(); $q->close();
    if (file_exists($imagePath)) unlink($imagePath);
    $d=$conn->prepare("DELETE FROM home_images WHERE id=?"); $d->bind_param("i",$imageId); $d->execute();
    $success="Image deleted!";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Home Page</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
    :root{--red:#da0416;--red-dark:#a30512;--navy:#0e2431;--navy-deep:#0e0f31;--body-bg:#e5ecfb;--white:#ffffff;--border:#e8e8e8;--muted:#6b7a8d;--input-bg:#f4f6fb;--sidebar-w:25rem}
    html{font-size:62.5%}body{background:var(--body-bg);font-family:'Poppins',sans-serif;font-size:1.4rem;color:var(--navy);margin:0}
    .wrapper{display:flex;min-height:100vh}.main-content{margin-left:var(--sidebar-w);flex:1;padding:3rem 3.2rem 5rem;overflow-y:auto}
    .page-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:3rem;padding-bottom:1.6rem;border-bottom:0.2rem solid var(--border);flex-wrap:wrap;gap:1.2rem}
    .page-header h2{font-size:2.4rem;font-weight:800;color:var(--navy);display:flex;align-items:center;gap:1rem;margin:0}
    .page-header h2 i{color:var(--red)}
    .form-card{background:var(--white);border:0.1rem solid var(--border);border-radius:1.2rem;padding:2.4rem;margin-bottom:2.4rem;box-shadow:0 0.2rem 1rem rgba(14,36,49,0.06)}
    .form-card-title{font-size:1.6rem;font-weight:700;color:var(--navy);margin-bottom:2rem;display:flex;align-items:center;gap:0.8rem;padding-bottom:1.2rem;border-bottom:0.15rem solid var(--border)}
    .form-card-title i{width:3.2rem;height:3.2rem;background:linear-gradient(135deg,var(--red),var(--red-dark));color:#fff;border-radius:0.7rem;display:inline-flex;align-items:center;justify-content:center;font-size:1.4rem;flex-shrink:0;box-shadow:0 0.3rem 0.8rem rgba(218,4,22,0.25)}
    .form-label{font-size:1.2rem;font-weight:700;color:var(--navy);text-transform:uppercase;letter-spacing:0.06em;margin-bottom:0.5rem;display:block}
    .form-control{background:var(--input-bg);color:var(--navy);border:0.15rem solid var(--border);border-radius:0.8rem;padding:1rem 1.4rem;font-size:1.4rem;font-family:'Poppins',sans-serif;width:100%;transition:border-color 0.2s,box-shadow 0.2s}
    .form-control:focus{background:var(--white);border-color:var(--red);box-shadow:0 0 0 0.3rem rgba(218,4,22,0.1);outline:none;color:var(--navy)}
    textarea.form-control{resize:vertical;min-height:9rem}
    .form-text{font-size:1.15rem;color:var(--muted);margin-top:0.4rem}
    .upload-zone{border:0.2rem dashed var(--border);border-radius:1rem;padding:2rem;text-align:center;cursor:pointer;transition:border-color 0.2s,background 0.2s;position:relative}
    .upload-zone:hover{border-color:var(--red);background:rgba(218,4,22,0.03)}
    .upload-zone input[type="file"]{position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%}
    .upload-zone i{font-size:2.6rem;color:var(--red);margin-bottom:0.6rem;display:block}
    .upload-zone p{font-size:1.3rem;color:var(--muted);margin:0;line-height:1.6}
    .upload-zone strong{color:var(--navy)}
    .alert{padding:1.4rem 1.8rem;border-radius:0.8rem;font-size:1.4rem;font-weight:600;margin-bottom:2rem;display:flex;align-items:center;gap:1rem;border:none}
    .alert-success{background:rgba(34,197,94,0.12);color:#166534;border-left:0.4rem solid #22c55e}
    .alert-danger{background:rgba(218,4,22,0.08);color:var(--red-dark);border-left:0.4rem solid var(--red)}
    .btn-save{display:inline-flex;align-items:center;gap:0.8rem;background:linear-gradient(135deg,var(--red),var(--red-dark));color:#fff;font-size:1.4rem;font-weight:700;padding:1rem 2.4rem;border-radius:0.8rem;border:none;cursor:pointer;font-family:'Poppins',sans-serif;box-shadow:0 0.4rem 1.4rem rgba(218,4,22,0.3);transition:transform 0.2s,box-shadow 0.2s}
    .btn-save:hover{transform:translateY(-0.2rem);box-shadow:0 0.8rem 2rem rgba(218,4,22,0.45);color:#fff}
    .gallery-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(12rem,1fr));gap:1rem;margin-top:1.2rem}
    .gallery-item{position:relative;border-radius:0.8rem;overflow:hidden;aspect-ratio:1;box-shadow:0 0.2rem 0.8rem rgba(14,36,49,0.08)}
    .gallery-item img{width:100%;height:100%;object-fit:cover;transition:transform 0.3s}
    .gallery-item:hover img{transform:scale(1.06)}
    .gallery-item-delete{position:absolute;top:0.5rem;right:0.5rem;width:2.6rem;height:2.6rem;background:rgba(218,4,22,0.9);color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.1rem;text-decoration:none;opacity:0;transition:opacity 0.2s}
    .gallery-item:hover .gallery-item-delete{opacity:1}
    .toggle-btn{display:none;position:fixed;top:1.4rem;left:1.4rem;font-size:2.2rem;background:none;border:none;color:var(--navy);cursor:pointer;z-index:1100;transition:color 0.2s}
    .toggle-btn:hover{color:var(--red)}
    @media(max-width:768px){html{font-size:56%}.main-content{margin-left:0;padding:6rem 1.6rem 4rem}.toggle-btn{display:block}}
    </style>
</head>
<body>
<button class="toggle-btn" id="menuToggle"><i class="fas fa-bars"></i></button>
<div class="wrapper">
    <?php include '../includes/sidebar.php'; ?>
    <div class="main-content">

        <div class="page-header">
            <h2><i class="fas fa-house-chimney-window"></i> Edit Home Page</h2>
        </div>

        <?php if(isset($success)): ?><div class="alert alert-success"><i class="fas fa-circle-check"></i> <?=$success?></div><?php endif; ?>
        <?php if(isset($error)): ?><div class="alert alert-danger"><i class="fas fa-circle-exclamation"></i> <?=$error?></div><?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="row g-4">
                <div class="col-lg-7">
                    <div class="form-card">
                        <div class="form-card-title"><i class="fas fa-pen-nib"></i> Content</div>
                        <div class="mb-3">
                            <label class="form-label">Greetings Text</label>
                            <textarea name="greetings" class="form-control" rows="2" placeholder="e.g. Hello, I'm Juan"><?=htmlspecialchars($home['greetings']??'')?></textarea>
                        </div>
                        <div class="mb-0">
                            <label class="form-label">Typing Text <small style="font-size:1.1rem;color:var(--muted);font-weight:400;text-transform:none;">(comma-separated)</small></label>
                            <textarea name="typing_text" class="form-control" rows="3" placeholder="Web Developer, Designer, Freelancer"><?=htmlspecialchars($home['typing_text']??'')?></textarea>
                            <p class="form-text">Each word/phrase separated by a comma will be cycled through the typing animation.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="form-card">
                        <div class="form-card-title"><i class="fas fa-share-nodes"></i> Social Links</div>
                        <div class="mb-3">
                            <label class="form-label"><i class="fab fa-facebook" style="color:#1877f2;margin-right:0.5rem;"></i>Facebook</label>
                            <input type="text" name="facebook_link" class="form-control" value="<?=htmlspecialchars($home['facebook_link']??'')?>" placeholder="https://facebook.com/yourprofile">
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><i class="fab fa-instagram" style="color:#e1306c;margin-right:0.5rem;"></i>Instagram</label>
                            <input type="text" name="instagram_link" class="form-control" value="<?=htmlspecialchars($home['instagram_link']??'')?>" placeholder="https://instagram.com/yourprofile">
                        </div>
                        <div class="mb-0">
                            <label class="form-label"><i class="fab fa-linkedin" style="color:#0a66c2;margin-right:0.5rem;"></i>LinkedIn</label>
                            <input type="text" name="youtube_link" class="form-control" value="<?=htmlspecialchars($home['youtube_link']??'')?>" placeholder="https://linkedin.com/in/yourprofile">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Hero Images -->
            <div class="form-card">
                <div class="form-card-title"><i class="fas fa-images"></i> Hero Images</div>
                <div class="upload-zone">
                    <input type="file" name="hero_images[]" accept="image/*" multiple>
                    <i class="fas fa-cloud-arrow-up"></i>
                    <p><strong>Click to upload</strong> multiple hero images</p>
                    <p>These cycle in the homepage hero section</p>
                </div>

                <?php
                $imageResult = $conn->query("SELECT * FROM home_images");
                $heroImages = $imageResult->fetch_all(MYSQLI_ASSOC);
                ?>
                <?php if(!empty($heroImages)): ?>
                    <div style="margin-top:1.6rem;">
                        <p style="font-size:1.25rem;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:0.06em;margin-bottom:1rem;">Current Hero Images</p>
                        <div class="gallery-grid">
                            <?php foreach($heroImages as $img): ?>
                            <div class="gallery-item">
                                <img src="<?=htmlspecialchars($img['image_path'])?>" alt="Hero Image">
                                <a href="?delete_image_id=<?=$img['id']?>" class="gallery-item-delete" onclick="return confirm('Delete this image?')">
                                    <i class="fas fa-xmark"></i>
                                </a>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="d-flex justify-content-end">
                <button type="submit" class="btn-save"><i class="fas fa-floppy-disk"></i> Save Changes</button>
            </div>
        </form>
    </div>
</div>
<script>
const menuToggle=document.getElementById('menuToggle'),sidebar=document.getElementById('sidebar');
if(menuToggle&&sidebar)menuToggle.addEventListener('click',()=>sidebar.classList.toggle('active'));
document.querySelectorAll('.upload-zone input[type="file"]').forEach(i=>{
    i.addEventListener('change',function(){const l=this.closest('.upload-zone').querySelector('p strong');if(l&&this.files.length)l.textContent=this.files.length>1?this.files.length+' files selected':this.files[0].name;});
});
</script>
</body></html>