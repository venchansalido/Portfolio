<?php
session_start();
include '../includes/config.php';
include '../includes/restriction_admin.php';
include '../includes/session_utils.php';
if (!isset($_SESSION['user_id'])||$_SESSION['role']!=='admin'||!checkSessionExpiration()){session_unset();session_destroy();header('Location: ../index.php');exit;}

if ($_SERVER['REQUEST_METHOD']==='POST'){
    if(isset($_POST['add_timeline'])){
        $s=$conn->prepare("INSERT INTO timeline_items (position,title,description,status) VALUES (?,?,?,?)");
        $s->bind_param("ssss",$_POST['position'],$_POST['title'],$_POST['description'],$_POST['status']);
        $_SESSION[$s->execute()?'success':'error']=$s->execute()?"Timeline item added!":"Error: ".$conn->error;
    } elseif(isset($_POST['update_timeline'])){
        $s=$conn->prepare("UPDATE timeline_items SET position=?,title=?,description=?,status=? WHERE id=?");
        $s->bind_param("ssssi",$_POST['position'],$_POST['title'],$_POST['description'],$_POST['status'],$_POST['id']);
        $_SESSION[$s->execute()?'success':'error']=$s->execute()?"Timeline item updated!":"Error: ".$conn->error;
    }
}
if(isset($_GET['delete'])){
    $s=$conn->prepare("DELETE FROM timeline_items WHERE id=?"); $s->bind_param("i",$_GET['delete']);
    $_SESSION[$s->execute()?'success':'error']=$s->execute()?"Item deleted!":"Error: ".$conn->error;
    header("Location: timeline.php"); exit();
}
$timelineItems=$conn->query("SELECT * FROM timeline_items ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
$editItem=null;
if(isset($_GET['edit'])){$s=$conn->prepare("SELECT * FROM timeline_items WHERE id=?");$s->bind_param("i",$_GET['edit']);$s->execute();$editItem=$s->get_result()->fetch_assoc();}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Timeline Management</title>
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
    .form-card-title i{width:3.2rem;height:3.2rem;background:linear-gradient(135deg,var(--red),var(--red-dark));color:#fff;border-radius:0.7rem;display:inline-flex;align-items:center;justify-content:center;font-size:1.4rem;flex-shrink:0}
    .form-label{font-size:1.2rem;font-weight:700;color:var(--navy);text-transform:uppercase;letter-spacing:0.06em;margin-bottom:0.5rem;display:block}
    .form-control,.form-select{background:var(--input-bg);color:var(--navy);border:0.15rem solid var(--border);border-radius:0.8rem;padding:1rem 1.4rem;font-size:1.4rem;font-family:'Poppins',sans-serif;width:100%;transition:border-color 0.2s,box-shadow 0.2s}
    .form-control:focus,.form-select:focus{background:var(--white);border-color:var(--red);box-shadow:0 0 0 0.3rem rgba(218,4,22,0.1);outline:none;color:var(--navy)}
    textarea.form-control{resize:vertical;min-height:10rem}
    .alert{padding:1.4rem 1.8rem;border-radius:0.8rem;font-size:1.4rem;font-weight:600;margin-bottom:2rem;display:flex;align-items:center;gap:1rem;border:none}
    .alert-success{background:rgba(34,197,94,0.12);color:#166534;border-left:0.4rem solid #22c55e}
    .alert-danger{background:rgba(218,4,22,0.08);color:var(--red-dark);border-left:0.4rem solid var(--red)}
    .alert-info{background:rgba(14,36,49,0.06);color:var(--navy);border-left:0.4rem solid var(--navy)}
    .btn-save{display:inline-flex;align-items:center;gap:0.8rem;background:linear-gradient(135deg,var(--red),var(--red-dark));color:#fff;font-size:1.4rem;font-weight:700;padding:1rem 2.4rem;border-radius:0.8rem;border:none;cursor:pointer;font-family:'Poppins',sans-serif;box-shadow:0 0.4rem 1.4rem rgba(218,4,22,0.3);transition:transform 0.2s,box-shadow 0.2s}
    .btn-save:hover{transform:translateY(-0.2rem);box-shadow:0 0.8rem 2rem rgba(218,4,22,0.45);color:#fff}
    .btn-outline-navy{display:inline-flex;align-items:center;gap:0.6rem;background:transparent;color:var(--navy);font-size:1.35rem;font-weight:600;padding:0.8rem 1.6rem;border-radius:0.7rem;border:0.15rem solid var(--navy);cursor:pointer;font-family:'Poppins',sans-serif;text-decoration:none;transition:background 0.2s,color 0.2s}
    .btn-outline-navy:hover{background:var(--navy);color:#fff}
    .btn-outline-danger{display:inline-flex;align-items:center;gap:0.6rem;background:transparent;color:var(--red);font-size:1.35rem;font-weight:600;padding:0.8rem 1.6rem;border-radius:0.7rem;border:0.15rem solid var(--red);cursor:pointer;font-family:'Poppins',sans-serif;text-decoration:none;transition:background 0.2s,color 0.2s}
    .btn-outline-danger:hover{background:var(--red);color:#fff}
    .item-card{background:var(--white);border:0.1rem solid var(--border);border-left:0.35rem solid var(--red);border-radius:0.8rem;padding:1.8rem 2rem;margin-bottom:1.2rem;display:flex;justify-content:space-between;align-items:flex-start;gap:1.4rem;box-shadow:0 0.1rem 0.6rem rgba(14,36,49,0.05);transition:box-shadow 0.2s,transform 0.2s}
    .item-card:hover{box-shadow:0 0.4rem 1.4rem rgba(14,36,49,0.1);transform:translateY(-0.1rem)}
    .item-card.right{border-left-color:var(--navy)}
    .item-card-title{font-size:1.5rem;font-weight:700;color:var(--navy);margin-bottom:0.4rem}
    .item-card-body{font-size:1.35rem;color:var(--muted)}
    .item-badge{display:inline-flex;align-items:center;padding:0.3rem 1rem;background:rgba(218,4,22,0.1);color:var(--red);border-radius:2rem;font-size:1.2rem;font-weight:700;margin-bottom:0.6rem}
    .item-badge.navy{background:rgba(14,36,49,0.08);color:var(--navy)}
    .item-actions{display:flex;gap:0.8rem;flex-shrink:0}
    .items-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(36rem,1fr));gap:1.2rem}
    .toggle-btn{display:none;position:fixed;top:1.4rem;left:1.4rem;font-size:2.2rem;background:none;border:none;color:var(--navy);cursor:pointer;z-index:1100;transition:color 0.2s}
    .toggle-btn:hover{color:var(--red)}
    @media(max-width:768px){html{font-size:56%}.main-content{margin-left:0;padding:6rem 1.6rem 4rem}.toggle-btn{display:block}.items-grid{grid-template-columns:1fr}}
    </style>
</head>
<body>
<button class="toggle-btn" id="menuToggle"><i class="fas fa-bars"></i></button>
<div class="wrapper">
    <?php include '../includes/sidebar.php'; ?>
    <div class="main-content">

        <div class="page-header">
            <h2><i class="fas fa-timeline"></i> Timeline Management</h2>
        </div>

        <?php if(isset($_SESSION['success'])): ?><div class="alert alert-success"><i class="fas fa-circle-check"></i> <?=$_SESSION['success'];unset($_SESSION['success']);?></div><?php endif; ?>
        <?php if(isset($_SESSION['error'])): ?><div class="alert alert-danger"><i class="fas fa-circle-exclamation"></i> <?=$_SESSION['error'];unset($_SESSION['error']);?></div><?php endif; ?>

        <!-- Add / Edit Form -->
        <div class="form-card">
            <div class="form-card-title">
                <i class="fas <?=$editItem?'fa-pen':'fa-plus'?>"></i>
                <?=$editItem?'Edit':'Add New'?> Timeline Item
            </div>
            <form method="POST">
                <?php if($editItem): ?><input type="hidden" name="id" value="<?=$editItem['id']?>"><?php endif; ?>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Position</label>
                        <select name="position" class="form-select" required>
                            <option value="left" <?=($editItem&&$editItem['position']=='left')?'selected':''?>>Left</option>
                            <option value="right" <?=($editItem&&$editItem['position']=='right')?'selected':''?>>Right</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <input type="text" name="status" class="form-control" value="<?=$editItem?htmlspecialchars($editItem['status']):'on progress'?>" required placeholder="e.g. Completed, On Progress">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" class="form-control" value="<?=$editItem?htmlspecialchars($editItem['title']):''?>" required placeholder="e.g. Started College">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" required placeholder="Describe this timeline milestone..."><?=$editItem?htmlspecialchars($editItem['description']):''?></textarea>
                    </div>
                </div>
                <div class="d-flex gap-2 justify-content-end mt-3">
                    <?php if($editItem): ?><a href="timeline.php" class="btn-outline-navy"><i class="fas fa-xmark"></i> Cancel</a><?php endif; ?>
                    <button type="submit" name="<?=$editItem?'update_timeline':'add_timeline'?>" class="btn-save">
                        <i class="fas fa-floppy-disk"></i> <?=$editItem?'Update':'Add'?> Item
                    </button>
                </div>
            </form>
        </div>

        <!-- List -->
        <div class="form-card">
            <div class="form-card-title"><i class="fas fa-list"></i> Current Timeline Items</div>
            <?php if(empty($timelineItems)): ?>
                <div class="alert alert-info" style="margin:0;"><i class="fas fa-info-circle"></i> No timeline items yet. Add one above.</div>
            <?php else: ?>
                <div class="items-grid">
                <?php foreach($timelineItems as $item): ?>
                    <div class="item-card <?=$item['position']?>">
                        <div style="flex:1;">
                            <div class="item-badge <?=$item['position']=='right'?'navy':''?>"><?=htmlspecialchars($item['status'])?></div>
                            <div class="item-card-title"><?=htmlspecialchars($item['title'])?></div>
                            <div class="item-card-body"><?=htmlspecialchars(substr($item['description'],0,120)).(strlen($item['description'])>120?'…':'')?></div>
                        </div>
                        <div class="item-actions">
                            <a href="timeline.php?edit=<?=$item['id']?>" class="btn-outline-navy" style="padding:0.7rem 1rem;"><i class="fas fa-pen"></i></a>
                            <a href="timeline.php?delete=<?=$item['id']?>" class="btn-outline-danger" style="padding:0.7rem 1rem;" onclick="return confirm('Delete this item?')"><i class="fas fa-trash"></i></a>
                        </div>
                    </div>
                <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script>
const menuToggle=document.getElementById('menuToggle'),sidebar=document.getElementById('sidebar');
if(menuToggle&&sidebar)menuToggle.addEventListener('click',()=>sidebar.classList.toggle('active'));
</script>
</body></html>