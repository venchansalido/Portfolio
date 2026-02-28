<?php
session_start();
include '../includes/config.php';
include '../includes/restriction_admin.php';
include '../includes/session_utils.php';
if (!isset($_SESSION['user_id'])||$_SESSION['role']!=='admin'||!checkSessionExpiration()){session_unset();session_destroy();header('Location: ../index.php');exit;}

function uploadFile($file,$dir,$prefix,$pid=null){
    $ext=pathinfo($file['name'],PATHINFO_EXTENSION);
    $name=$prefix.'_'.time().'_'.($pid??'').'_'.uniqid().'.'.$ext;
    return move_uploaded_file($file['tmp_name'],$dir.$name)?$dir.$name:false;
}

if($_SERVER['REQUEST_METHOD']==='POST'){
    if(isset($_POST['add_project'])||isset($_POST['update_project'])){
        $isUpd=isset($_POST['update_project']);
        $title=$_POST['title']; $desc=$_POST['description']; $url=$_POST['url']??null; $id=$isUpd?$_POST['id']:null;
        if(empty($title)||empty($desc)){$_SESSION['error']="Title and description required!";header("Location: projects.php");exit();}
        $thumbPath=null;
        if($isUpd){$s=$conn->prepare("SELECT thumbnail_path FROM projects WHERE id=?");$s->bind_param("i",$id);$s->execute();$r=$s->get_result();$thumbPath=$r->num_rows>0?$r->fetch_assoc()['thumbnail_path']:null;}
        if(isset($_FILES['thumbnail'])&&$_FILES['thumbnail']['error']===UPLOAD_ERR_OK){
            if($isUpd&&$thumbPath&&file_exists($thumbPath))unlink($thumbPath);
            $nt=uploadFile($_FILES['thumbnail'],'../assets/images/projects/','thumb',$id);
            if($nt===false){$_SESSION['error']="Thumbnail upload failed!";header("Location: projects.php");exit();}
            $thumbPath=$nt;
        }
        if($isUpd){$s=$conn->prepare("UPDATE projects SET title=?,description=?,thumbnail_path=?,url=? WHERE id=?");$s->bind_param("ssssi",$title,$desc,$thumbPath,$url,$id);}
        else{$s=$conn->prepare("INSERT INTO projects (title,description,thumbnail_path,url) VALUES (?,?,?,?)");$s->bind_param("ssss",$title,$desc,$thumbPath,$url);}
        if($s->execute()){
            $pid=$isUpd?$id:$conn->insert_id;
            if(!empty($_FILES['gallery_images']['name'][0])){
                foreach($_FILES['gallery_images']['tmp_name'] as $k=>$tmp){
                    if($_FILES['gallery_images']['error'][$k]===UPLOAD_ERR_OK){
                        $ip=uploadFile(['name'=>$_FILES['gallery_images']['name'][$k],'tmp_name'=>$tmp,'error'=>$_FILES['gallery_images']['error'][$k]],'../assets/images/projects/gallery/','gallery',$pid);
                        if($ip){$ins=$conn->prepare("INSERT INTO project_gallery (project_id,image_path) VALUES (?,?)");$ins->bind_param("is",$pid,$ip);$ins->execute();}
                    }
                }
            }
            $_SESSION['success']=$isUpd?"Project updated!":"Project added!";
        } else {$_SESSION['error']="Error: ".$conn->error;}
        header("Location: projects.php"); exit();
    }
}
if(isset($_GET['delete'])&&is_numeric($_GET['delete'])){
    $id=$_GET['delete'];
    $s=$conn->prepare("SELECT thumbnail_path FROM projects WHERE id=?");$s->bind_param("i",$id);$s->execute();$tp=$s->get_result()->fetch_assoc()['thumbnail_path'];
    $gs=$conn->prepare("SELECT image_path FROM project_gallery WHERE project_id=?");$gs->bind_param("i",$id);$gs->execute();$gis=$gs->get_result()->fetch_all(MYSQLI_ASSOC);
    $d=$conn->prepare("DELETE FROM projects WHERE id=?");$d->bind_param("i",$id);
    if($d->execute()){if($tp&&file_exists($tp))unlink($tp);foreach($gis as $g){if(file_exists($g['image_path']))unlink($g['image_path']);}$_SESSION['success']="Project deleted!";}
    else{$_SESSION['error']="Error: ".$conn->error;}
    header("Location: projects.php");exit();
}
if(isset($_GET['delete_image'])&&isset($_GET['project_id'])){
    $iid=$_GET['delete_image']; $pid=$_GET['project_id'];
    $s=$conn->prepare("SELECT image_path FROM project_gallery WHERE id=?");$s->bind_param("i",$iid);$s->execute();$ip=$s->get_result()->fetch_assoc()['image_path'];
    $d=$conn->prepare("DELETE FROM project_gallery WHERE id=?");$d->bind_param("i",$iid);
    if($d->execute()){if($ip&&file_exists($ip))unlink($ip);$_SESSION['success']="Image deleted!";}
    header("Location: projects.php?edit=".$pid);exit();
}
$projects=$conn->query("SELECT * FROM projects ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
$editProject=null; $galleryImages=[];
if(isset($_GET['edit'])&&is_numeric($_GET['edit'])){
    $s=$conn->prepare("SELECT * FROM projects WHERE id=?");$s->bind_param("i",$_GET['edit']);$s->execute();$r=$s->get_result();
    if($r->num_rows===0){$_SESSION['error']="Project not found!";header("Location: projects.php");exit();}
    $editProject=$r->fetch_assoc();
    $gs=$conn->prepare("SELECT * FROM project_gallery WHERE project_id=?");$gs->bind_param("i",$_GET['edit']);$gs->execute();$galleryImages=$gs->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projects Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
    :root{--red:#da0416;--red-dark:#a30512;--navy:#0e2431;--navy-deep:#0e0f31;--body-bg:#e5ecfb;--white:#ffffff;--border:#e8e8e8;--muted:#6b7a8d;--input-bg:#f4f6fb;--sidebar-w:25rem}
    html{font-size:62.5%}body{background:var(--body-bg);font-family:'Poppins',sans-serif;font-size:1.4rem;color:var(--navy);margin:0}
    .wrapper{display:flex;min-height:100vh}.main-content{margin-left:var(--sidebar-w);flex:1;padding:3rem 3.2rem 5rem;overflow-y:auto}
    .page-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:3rem;padding-bottom:1.6rem;border-bottom:0.2rem solid var(--border);flex-wrap:wrap;gap:1.2rem}
    .page-header h2{font-size:2.4rem;font-weight:800;color:var(--navy);display:flex;align-items:center;gap:1rem;margin:0}.page-header h2 i{color:var(--red)}
    .form-card{background:var(--white);border:0.1rem solid var(--border);border-radius:1.2rem;padding:2.4rem;margin-bottom:2.4rem;box-shadow:0 0.2rem 1rem rgba(14,36,49,0.06)}
    .form-card-title{font-size:1.6rem;font-weight:700;color:var(--navy);margin-bottom:2rem;display:flex;align-items:center;gap:0.8rem;padding-bottom:1.2rem;border-bottom:0.15rem solid var(--border)}
    .form-card-title i{width:3.2rem;height:3.2rem;background:linear-gradient(135deg,var(--red),var(--red-dark));color:#fff;border-radius:0.7rem;display:inline-flex;align-items:center;justify-content:center;font-size:1.4rem;flex-shrink:0}
    .form-label{font-size:1.2rem;font-weight:700;color:var(--navy);text-transform:uppercase;letter-spacing:0.06em;margin-bottom:0.5rem;display:block}
    .form-control{background:var(--input-bg);color:var(--navy);border:0.15rem solid var(--border);border-radius:0.8rem;padding:1rem 1.4rem;font-size:1.4rem;font-family:'Poppins',sans-serif;width:100%;transition:border-color 0.2s,box-shadow 0.2s}
    .form-control:focus{background:var(--white);border-color:var(--red);box-shadow:0 0 0 0.3rem rgba(218,4,22,0.1);outline:none;color:var(--navy)}
    textarea.form-control{resize:vertical;min-height:10rem}
    .upload-zone{border:0.2rem dashed var(--border);border-radius:1rem;padding:2rem;text-align:center;cursor:pointer;transition:border-color 0.2s,background 0.2s;position:relative}
    .upload-zone:hover{border-color:var(--red);background:rgba(218,4,22,0.03)}
    .upload-zone input[type="file"]{position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%}
    .upload-zone i{font-size:2.4rem;color:var(--red);margin-bottom:0.6rem;display:block}
    .upload-zone p{font-size:1.3rem;color:var(--muted);margin:0;line-height:1.6}.upload-zone strong{color:var(--navy)}
    .alert{padding:1.4rem 1.8rem;border-radius:0.8rem;font-size:1.4rem;font-weight:600;margin-bottom:2rem;display:flex;align-items:center;gap:1rem;border:none}
    .alert-success{background:rgba(34,197,94,0.12);color:#166534;border-left:0.4rem solid #22c55e}
    .alert-danger{background:rgba(218,4,22,0.08);color:var(--red-dark);border-left:0.4rem solid var(--red)}
    .alert-info{background:rgba(14,36,49,0.06);color:var(--navy);border-left:0.4rem solid var(--navy)}
    .btn-save{display:inline-flex;align-items:center;gap:0.8rem;background:linear-gradient(135deg,var(--red),var(--red-dark));color:#fff;font-size:1.4rem;font-weight:700;padding:1rem 2.4rem;border-radius:0.8rem;border:none;cursor:pointer;font-family:'Poppins',sans-serif;box-shadow:0 0.4rem 1.4rem rgba(218,4,22,0.3);transition:transform 0.2s,box-shadow 0.2s}
    .btn-save:hover{transform:translateY(-0.2rem);box-shadow:0 0.8rem 2rem rgba(218,4,22,0.45);color:#fff}
    .btn-outline-navy{display:inline-flex;align-items:center;gap:0.6rem;background:transparent;color:var(--navy);font-size:1.35rem;font-weight:600;padding:0.7rem 1.4rem;border-radius:0.7rem;border:0.15rem solid var(--navy);cursor:pointer;font-family:'Poppins',sans-serif;text-decoration:none;transition:background 0.2s,color 0.2s}
    .btn-outline-navy:hover{background:var(--navy);color:#fff}
    .btn-outline-danger{display:inline-flex;align-items:center;gap:0.6rem;background:transparent;color:var(--red);font-size:1.35rem;font-weight:600;padding:0.7rem 1.4rem;border-radius:0.7rem;border:0.15rem solid var(--red);cursor:pointer;font-family:'Poppins',sans-serif;text-decoration:none;transition:background 0.2s,color 0.2s}
    .btn-outline-danger:hover{background:var(--red);color:#fff}
    .table{width:100%;border-collapse:collapse;font-size:1.35rem}
    .table th{background:var(--input-bg);color:var(--navy);font-weight:700;font-size:1.2rem;text-transform:uppercase;letter-spacing:0.05em;padding:1.2rem 1.4rem;border-bottom:0.2rem solid var(--border);text-align:left}
    .table td{padding:1.2rem 1.4rem;border-bottom:0.1rem solid var(--border);color:var(--navy);vertical-align:middle}
    .table tbody tr:hover{background:rgba(218,4,22,0.03)}
    .table-responsive{overflow-x:auto}
    .thumb-preview{width:8rem;height:6rem;object-fit:cover;border-radius:0.6rem;box-shadow:0 0.2rem 0.6rem rgba(14,36,49,0.1)}
    .gallery-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(10rem,1fr));gap:1rem;margin-top:1.2rem}
    .gallery-item{position:relative;border-radius:0.8rem;overflow:hidden;aspect-ratio:1;box-shadow:0 0.2rem 0.8rem rgba(14,36,49,0.08)}
    .gallery-item img{width:100%;height:100%;object-fit:cover;transition:transform 0.3s}
    .gallery-item:hover img{transform:scale(1.06)}
    .gallery-item-delete{position:absolute;top:0.5rem;right:0.5rem;width:2.6rem;height:2.6rem;background:rgba(218,4,22,0.9);color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.1rem;text-decoration:none;opacity:0;transition:opacity 0.2s}
    .gallery-item:hover .gallery-item-delete{opacity:1}
    .photo-preview{display:flex;align-items:center;gap:1.4rem;margin-top:1.2rem;padding:1.2rem;background:var(--input-bg);border-radius:0.8rem;border:0.1rem solid var(--border)}
    .photo-preview-label{font-size:1.25rem;color:var(--muted)}.photo-preview-label strong{display:block;color:var(--navy);font-size:1.3rem;margin-bottom:0.2rem}
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
            <h2><i class="fas fa-folder-open"></i> Projects Management</h2>
        </div>

        <?php if(isset($_SESSION['success'])): ?><div class="alert alert-success"><i class="fas fa-circle-check"></i> <?=$_SESSION['success'];unset($_SESSION['success']);?></div><?php endif; ?>
        <?php if(isset($_SESSION['error'])): ?><div class="alert alert-danger"><i class="fas fa-circle-exclamation"></i> <?=$_SESSION['error'];unset($_SESSION['error']);?></div><?php endif; ?>

        <!-- Form -->
        <div class="form-card">
            <div class="form-card-title">
                <i class="fas <?=$editProject?'fa-pen':'fa-plus'?>"></i>
                <?=$editProject?'Edit':'Add New'?> Project
            </div>
            <form method="POST" enctype="multipart/form-data">
                <?php if($editProject): ?><input type="hidden" name="id" value="<?=$editProject['id']?>"><?php endif; ?>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Project Title</label>
                        <input type="text" name="title" class="form-control" value="<?=$editProject?htmlspecialchars($editProject['title']):''?>" required placeholder="e.g. Portfolio Website">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Project URL <small style="font-size:1.1rem;color:var(--muted);font-weight:400;text-transform:none;">(optional)</small></label>
                        <input type="url" name="url" class="form-control" value="<?=$editProject?htmlspecialchars($editProject['url']??''):''?>" placeholder="https://example.com">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" required placeholder="Describe your project..."><?=$editProject?htmlspecialchars($editProject['description']):''?></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Thumbnail Image</label>
                        <div class="upload-zone">
                            <input type="file" name="thumbnail" accept="image/*">
                            <i class="fas fa-image"></i>
                            <p><strong>Click to upload</strong> thumbnail</p>
                            <p>Displayed as project card image</p>
                        </div>
                        <?php if($editProject&&$editProject['thumbnail_path']): ?>
                        <div class="photo-preview">
                            <img src="<?=htmlspecialchars($editProject['thumbnail_path'])?>" class="thumb-preview" alt="Thumbnail">
                            <div class="photo-preview-label"><strong>Current Thumbnail</strong>Upload a new file to replace</div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Gallery Images <small style="font-size:1.1rem;color:var(--muted);font-weight:400;text-transform:none;">(optional)</small></label>
                        <div class="upload-zone">
                            <input type="file" name="gallery_images[]" accept="image/*" multiple>
                            <i class="fas fa-images"></i>
                            <p><strong>Click to upload</strong> multiple images</p>
                            <p>Shown in project gallery modal</p>
                        </div>
                        <?php if(!empty($galleryImages)): ?>
                        <div class="gallery-grid">
                            <?php foreach($galleryImages as $img): ?>
                            <div class="gallery-item">
                                <img src="<?=htmlspecialchars($img['image_path'])?>" alt="Gallery">
                                <a href="projects.php?delete_image=<?=$img['id']?>&project_id=<?=$editProject['id']?>" class="gallery-item-delete" onclick="return confirm('Delete this image?')"><i class="fas fa-xmark"></i></a>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="d-flex gap-2 justify-content-end mt-3">
                    <?php if($editProject): ?><a href="projects.php" class="btn-outline-navy"><i class="fas fa-xmark"></i> Cancel</a><?php endif; ?>
                    <button type="submit" name="<?=$editProject?'update_project':'add_project'?>" class="btn-save">
                        <i class="fas fa-floppy-disk"></i> <?=$editProject?'Update':'Add'?> Project
                    </button>
                </div>
            </form>
        </div>

        <!-- Projects Table -->
        <div class="form-card">
            <div class="form-card-title"><i class="fas fa-list"></i> Current Projects</div>
            <?php if(empty($projects)): ?>
                <div class="alert alert-info" style="margin:0;"><i class="fas fa-info-circle"></i> No projects found. Add one above.</div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead><tr><th>Thumbnail</th><th>Title</th><th>Description</th><th>Gallery</th><th>Actions</th></tr></thead>
                    <tbody>
                    <?php foreach($projects as $p):
                        $s=$conn->prepare("SELECT COUNT(*) as c FROM project_gallery WHERE project_id=?");$s->bind_param("i",$p['id']);$s->execute();$gc=$s->get_result()->fetch_assoc()['c'];
                    ?>
                        <tr>
                            <td><?php if($p['thumbnail_path']): ?><img src="<?=htmlspecialchars($p['thumbnail_path'])?>" class="thumb-preview" alt="Thumb"><?php else: ?><div style="width:8rem;height:6rem;background:var(--input-bg);border-radius:0.6rem;display:flex;align-items:center;justify-content:center;"><i class="fas fa-image" style="color:var(--muted);font-size:2rem;"></i></div><?php endif; ?></td>
                            <td style="font-weight:600;"><?=htmlspecialchars($p['title'])?></td>
                            <td style="color:var(--muted);"><?=htmlspecialchars(substr($p['description'],0,60)).(strlen($p['description'])>60?'…':'')?></td>
                            <td><span style="background:rgba(14,36,49,0.08);color:var(--navy);padding:0.3rem 0.9rem;border-radius:2rem;font-size:1.2rem;font-weight:700;"><?=$gc?> imgs</span></td>
                            <td>
                                <div style="display:flex;gap:0.6rem;">
                                    <a href="projects.php?edit=<?=$p['id']?>" class="btn-outline-navy" style="padding:0.6rem 1rem;font-size:1.25rem;"><i class="fas fa-pen"></i></a>
                                    <a href="projects.php?delete=<?=$p['id']?>" class="btn-outline-danger" style="padding:0.6rem 1rem;font-size:1.25rem;" onclick="return confirm('Delete this project?')"><i class="fas fa-trash"></i></a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>

    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script>
const menuToggle=document.getElementById('menuToggle'),sidebar=document.getElementById('sidebar');
if(menuToggle&&sidebar)menuToggle.addEventListener('click',()=>sidebar.classList.toggle('active'));
document.querySelectorAll('.upload-zone input[type="file"]').forEach(i=>{
    i.addEventListener('change',function(){const l=this.closest('.upload-zone').querySelector('p strong');if(l&&this.files.length)l.textContent=this.files.length>1?this.files.length+' files selected':this.files[0].name;});
});
</script>
</body></html>