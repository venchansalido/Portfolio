<?php
session_start();
include '../includes/config.php';
include '../includes/restriction_admin.php';
include '../includes/session_utils.php';
if (!isset($_SESSION['user_id'])||$_SESSION['role']!=='admin'||!checkSessionExpiration()){session_unset();session_destroy();header('Location: ../index.php');exit;}
$userId=$_SESSION['user_id'];

if(isset($_POST['add_skill'])){
    $sn=$_POST['skill_name']; $ip='';
    if(isset($_FILES['skill_icon'])&&$_FILES['skill_icon']['error']==0){$n=basename($_FILES['skill_icon']['name']);$ip="../assets/images/skill_cms/".$n;move_uploaded_file($_FILES['skill_icon']['tmp_name'],$ip);}
    $s=$conn->prepare("INSERT INTO user_skills (user_id,skill_name,icon_path) VALUES (?,?,?)");$s->bind_param("iss",$userId,$sn,$ip);$s->execute();
    $_SESSION['success']="Skill added!";
}
if(isset($_POST['update_skill'])){
    $sid=$_POST['skill_id']; $sn=$_POST['skill_name'];
    if(isset($_FILES['skill_icon'])&&$_FILES['skill_icon']['error']==0){$n=basename($_FILES['skill_icon']['name']);$ip="../assets/images/skill_cms/".$n;move_uploaded_file($_FILES['skill_icon']['tmp_name'],$ip);$s=$conn->prepare("UPDATE user_skills SET skill_name=?,icon_path=? WHERE id=? AND user_id=?");$s->bind_param("ssii",$sn,$ip,$sid,$userId);}
    else{$s=$conn->prepare("UPDATE user_skills SET skill_name=? WHERE id=? AND user_id=?");$s->bind_param("sii",$sn,$sid,$userId);}
    $s->execute(); $_SESSION['success']="Skill updated!";
}
if(isset($_GET['delete_skill'])){$s=$conn->prepare("DELETE FROM user_skills WHERE id=? AND user_id=?");$s->bind_param("ii",$_GET['delete_skill'],$userId);$s->execute();$_SESSION['success']="Skill deleted!";header("Location: skills_edu.php#skills");exit();}

if(isset($_POST['add_education'])){
    $l=$_POST['level'];$sn=$_POST['school_name'];$c=$_POST['course']??null;$a=$_POST['address']??null;$sy=$_POST['start_year'];$ey=$_POST['end_year'];$ip='';
    if(isset($_FILES['education_image'])&&$_FILES['education_image']['error']==0){$n=basename($_FILES['education_image']['name']);$ip="../assets/images/edu_cms/".$n;move_uploaded_file($_FILES['education_image']['tmp_name'],$ip);}
    $s=$conn->prepare("INSERT INTO user_education (user_id,level,school_name,course,address,start_year,end_year,image_path) VALUES (?,?,?,?,?,?,?,?)");$s->bind_param("issssiis",$userId,$l,$sn,$c,$a,$sy,$ey,$ip);$s->execute();
    $_SESSION['success']="Education added!";
}
if(isset($_POST['update_education'])){
    $eid=$_POST['edu_id'];$l=$_POST['level'];$sn=$_POST['school_name'];$c=$_POST['course']??null;$a=$_POST['address']??null;$sy=$_POST['start_year'];$ey=$_POST['end_year'];
    if(isset($_FILES['education_image'])&&$_FILES['education_image']['error']==0){$n=basename($_FILES['education_image']['name']);$ip="../assets/images/edu_cms/".$n;move_uploaded_file($_FILES['education_image']['tmp_name'],$ip);$s=$conn->prepare("UPDATE user_education SET level=?,school_name=?,course=?,address=?,start_year=?,end_year=?,image_path=? WHERE id=? AND user_id=?");$s->bind_param("ssssiisii",$l,$sn,$c,$a,$sy,$ey,$ip,$eid,$userId);}
    else{$s=$conn->prepare("UPDATE user_education SET level=?,school_name=?,course=?,address=?,start_year=?,end_year=? WHERE id=? AND user_id=?");$s->bind_param("sssssiii",$l,$sn,$c,$a,$sy,$ey,$eid,$userId);}
    $s->execute(); $_SESSION['success']="Education updated!";
}
if(isset($_GET['delete_education'])){$s=$conn->prepare("DELETE FROM user_education WHERE id=? AND user_id=?");$s->bind_param("ii",$_GET['delete_education'],$userId);$s->execute();$_SESSION['success']="Education deleted!";header("Location: skills_edu.php#education");exit();}

$skills=$conn->query("SELECT * FROM user_skills WHERE user_id=$userId")->fetch_all(MYSQLI_ASSOC);
$education=$conn->query("SELECT * FROM user_education WHERE user_id=$userId ORDER BY FIELD(level,'Primary','Secondary','Tertiary')")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Skills & Education</title>
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
    .form-control,.form-select{background:var(--input-bg);color:var(--navy);border:0.15rem solid var(--border);border-radius:0.8rem;padding:1rem 1.4rem;font-size:1.4rem;font-family:'Poppins',sans-serif;width:100%;transition:border-color 0.2s,box-shadow 0.2s}
    .form-control:focus,.form-select:focus{background:var(--white);border-color:var(--red);box-shadow:0 0 0 0.3rem rgba(218,4,22,0.1);outline:none;color:var(--navy)}
    .upload-zone{border:0.2rem dashed var(--border);border-radius:1rem;padding:1.8rem;text-align:center;cursor:pointer;transition:border-color 0.2s,background 0.2s;position:relative}
    .upload-zone:hover{border-color:var(--red);background:rgba(218,4,22,0.03)}
    .upload-zone input[type="file"]{position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%}
    .upload-zone i{font-size:2rem;color:var(--red);margin-bottom:0.4rem;display:block}
    .upload-zone p{font-size:1.25rem;color:var(--muted);margin:0}.upload-zone strong{color:var(--navy)}
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
    /* Tabs */
    .tab-nav{display:flex;gap:0;border-bottom:0.2rem solid var(--border);margin-bottom:2.4rem}
    .tab-btn{background:none;border:none;padding:1.2rem 2.4rem;font-size:1.5rem;font-weight:700;color:var(--muted);cursor:pointer;font-family:'Poppins',sans-serif;border-bottom:0.25rem solid transparent;margin-bottom:-0.2rem;transition:color 0.2s,border-color 0.2s}
    .tab-btn.active{color:var(--red);border-bottom-color:var(--red)}
    .tab-pane{display:none}.tab-pane.active{display:block}
    /* Table */
    .table{width:100%;border-collapse:collapse;font-size:1.35rem}
    .table th{background:var(--input-bg);color:var(--navy);font-weight:700;font-size:1.2rem;text-transform:uppercase;letter-spacing:0.05em;padding:1.2rem 1.4rem;border-bottom:0.2rem solid var(--border);text-align:left}
    .table td{padding:1.2rem 1.4rem;border-bottom:0.1rem solid var(--border);color:var(--navy);vertical-align:middle}
    .table tbody tr:hover{background:rgba(218,4,22,0.03)}
    .table-responsive{overflow-x:auto}
    .skill-icon-img{width:4rem;height:4rem;object-fit:contain;border-radius:0.5rem;background:var(--input-bg);padding:0.3rem}
    .edu-img{width:6rem;height:5rem;object-fit:cover;border-radius:0.6rem}
    /* Modal */
    .modal-content{border-radius:1.2rem;border:none;box-shadow:0 1.2rem 4rem rgba(14,36,49,0.18)}
    .modal-header{border-bottom:0.1rem solid var(--border);padding:1.8rem 2.4rem}
    .modal-title{font-size:1.7rem;font-weight:700;color:var(--navy)}
    .modal-body{padding:2rem 2.4rem}
    .modal-footer{border-top:0.1rem solid var(--border);padding:1.4rem 2.4rem}
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
            <h2><i class="fas fa-laptop-code"></i> Skills & Education</h2>
        </div>

        <?php if(isset($_SESSION['success'])): ?><div class="alert alert-success"><i class="fas fa-circle-check"></i> <?=$_SESSION['success'];unset($_SESSION['success']);?></div><?php endif; ?>
        <?php if(isset($_SESSION['error'])): ?><div class="alert alert-danger"><i class="fas fa-circle-exclamation"></i> <?=$_SESSION['error'];unset($_SESSION['error']);?></div><?php endif; ?>

        <!-- Tabs -->
        <div class="tab-nav" id="tabNav">
            <button class="tab-btn active" data-tab="skills">
                <i class="fas fa-code" style="margin-right:0.6rem;"></i>Skills
            </button>
            <button class="tab-btn" data-tab="education">
                <i class="fas fa-graduation-cap" style="margin-right:0.6rem;"></i>Education
            </button>
        </div>

        <!-- ── SKILLS TAB ── -->
        <div class="tab-pane active" id="skills">

            <!-- Add Skill -->
            <div class="form-card">
                <div class="form-card-title"><i class="fas fa-plus"></i> Add New Skill</div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-5">
                            <label class="form-label">Skill Name</label>
                            <input type="text" name="skill_name" class="form-control" required placeholder="e.g. PHP, React, Figma">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Skill Icon</label>
                            <div class="upload-zone">
                                <input type="file" name="skill_icon" accept="image/*">
                                <i class="fas fa-image"></i>
                                <p><strong>Upload icon</strong> (PNG/SVG)</p>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" name="add_skill" class="btn-save" style="width:100%;justify-content:center;">
                                <i class="fas fa-plus"></i> Add
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Skills List -->
            <div class="form-card">
                <div class="form-card-title"><i class="fas fa-list"></i> Your Skills</div>
                <?php if(empty($skills)): ?>
                    <div class="alert alert-info" style="margin:0;"><i class="fas fa-info-circle"></i> No skills added yet.</div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead><tr><th>Icon</th><th>Skill Name</th><th>Actions</th></tr></thead>
                        <tbody>
                        <?php foreach($skills as $skill): ?>
                            <tr>
                                <td><?php if(!empty($skill['icon_path'])): ?><img src="<?=htmlspecialchars($skill['icon_path'])?>" class="skill-icon-img" alt=""><?php else: ?><div style="width:4rem;height:4rem;background:var(--input-bg);border-radius:0.5rem;display:flex;align-items:center;justify-content:center;"><i class="fas fa-code" style="color:var(--muted);"></i></div><?php endif; ?></td>
                                <td style="font-weight:600;"><?=htmlspecialchars($skill['skill_name'])?></td>
                                <td>
                                    <div style="display:flex;gap:0.6rem;">
                                        <button class="btn-outline-navy" style="padding:0.6rem 1rem;font-size:1.25rem;" data-bs-toggle="modal" data-bs-target="#editSkillModal<?=$skill['id']?>"><i class="fas fa-pen"></i></button>
                                        <a href="?delete_skill=<?=$skill['id']?>" class="btn-outline-danger" style="padding:0.6rem 1rem;font-size:1.25rem;" onclick="return confirm('Delete this skill?')"><i class="fas fa-trash"></i></a>
                                    </div>
                                </td>
                            </tr>
                            <!-- Edit Modal -->
                            <div class="modal fade" id="editSkillModal<?=$skill['id']?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title"><i class="fas fa-pen" style="color:var(--red);margin-right:0.6rem;"></i>Edit Skill</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form method="POST" enctype="multipart/form-data">
                                            <div class="modal-body">
                                                <input type="hidden" name="skill_id" value="<?=$skill['id']?>">
                                                <div class="mb-3">
                                                    <label class="form-label">Skill Name</label>
                                                    <input type="text" name="skill_name" class="form-control" value="<?=htmlspecialchars($skill['skill_name'])?>" required>
                                                </div>
                                                <div class="mb-0">
                                                    <label class="form-label">Skill Icon</label>
                                                    <?php if(!empty($skill['icon_path'])): ?>
                                                    <div class="mb-2"><img src="<?=htmlspecialchars($skill['icon_path'])?>" class="skill-icon-img" alt="Current"></div>
                                                    <?php endif; ?>
                                                    <div class="upload-zone"><input type="file" name="skill_icon" accept="image/*"><i class="fas fa-image"></i><p><strong>Upload new icon</strong> to replace</p></div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn-outline-navy" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" name="update_skill" class="btn-save"><i class="fas fa-floppy-disk"></i> Save</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- ── EDUCATION TAB ── -->
        <div class="tab-pane" id="education">

            <!-- Add Education -->
            <div class="form-card">
                <div class="form-card-title"><i class="fas fa-graduation-cap"></i> Add New Education</div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Level</label>
                            <select name="level" class="form-select" required>
                                <option value="Primary">Primary</option>
                                <option value="Secondary">Secondary</option>
                                <option value="Tertiary">Tertiary</option>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">School Name</label>
                            <input type="text" name="school_name" class="form-control" required placeholder="e.g. University of the Philippines">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Course <small style="font-size:1.1rem;color:var(--muted);font-weight:400;text-transform:none;">(if applicable)</small></label>
                            <input type="text" name="course" class="form-control" placeholder="e.g. BS Computer Science">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Address</label>
                            <input type="text" name="address" class="form-control" placeholder="City, Province">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Start Year</label>
                            <input type="number" name="start_year" min="1900" max="2099" class="form-control" required placeholder="2018">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">End Year</label>
                            <input type="number" name="end_year" min="1900" max="2099" class="form-control" required placeholder="2022">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">School Image</label>
                            <div class="upload-zone">
                                <input type="file" name="education_image" accept="image/*">
                                <i class="fas fa-school"></i>
                                <p><strong>Upload school photo</strong></p>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end mt-3">
                        <button type="submit" name="add_education" class="btn-save"><i class="fas fa-plus"></i> Add Education</button>
                    </div>
                </form>
            </div>

            <!-- Education List -->
            <div class="form-card">
                <div class="form-card-title"><i class="fas fa-list"></i> Your Education</div>
                <?php if(empty($education)): ?>
                    <div class="alert alert-info" style="margin:0;"><i class="fas fa-info-circle"></i> No education entries yet.</div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead><tr><th>Image</th><th>Level</th><th>School</th><th>Years</th><th>Actions</th></tr></thead>
                        <tbody>
                        <?php foreach($education as $edu): ?>
                            <tr>
                                <td><?php if(!empty($edu['image_path'])): ?><img src="<?=htmlspecialchars($edu['image_path'])?>" class="edu-img" alt=""><?php else: ?><div style="width:6rem;height:5rem;background:var(--input-bg);border-radius:0.6rem;display:flex;align-items:center;justify-content:center;"><i class="fas fa-school" style="color:var(--muted);font-size:2rem;"></i></div><?php endif; ?></td>
                                <td><span style="background:rgba(218,4,22,0.1);color:var(--red);padding:0.3rem 1rem;border-radius:2rem;font-size:1.2rem;font-weight:700;"><?=htmlspecialchars($edu['level'])?></span></td>
                                <td>
                                    <div style="font-weight:600;"><?=htmlspecialchars($edu['school_name'])?></div>
                                    <?php if(!empty($edu['course'])): ?><div style="font-size:1.25rem;color:var(--muted);"><?=htmlspecialchars($edu['course'])?></div><?php endif; ?>
                                </td>
                                <td style="font-weight:600;white-space:nowrap;"><?=htmlspecialchars($edu['start_year'])?> – <?=htmlspecialchars($edu['end_year'])?></td>
                                <td>
                                    <div style="display:flex;gap:0.6rem;">
                                        <button class="btn-outline-navy" style="padding:0.6rem 1rem;font-size:1.25rem;" data-bs-toggle="modal" data-bs-target="#editEduModal<?=$edu['id']?>"><i class="fas fa-pen"></i></button>
                                        <a href="?delete_education=<?=$edu['id']?>" class="btn-outline-danger" style="padding:0.6rem 1rem;font-size:1.25rem;" onclick="return confirm('Delete this entry?')"><i class="fas fa-trash"></i></a>
                                    </div>
                                </td>
                            </tr>
                            <!-- Edit Edu Modal -->
                            <div class="modal fade" id="editEduModal<?=$edu['id']?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-lg modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title"><i class="fas fa-pen" style="color:var(--red);margin-right:0.6rem;"></i>Edit Education</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form method="POST" enctype="multipart/form-data">
                                            <div class="modal-body">
                                                <input type="hidden" name="edu_id" value="<?=$edu['id']?>">
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label">Level</label>
                                                        <select name="level" class="form-select" required>
                                                            <option value="Primary" <?=$edu['level']=='Primary'?'selected':''?>>Primary</option>
                                                            <option value="Secondary" <?=$edu['level']=='Secondary'?'selected':''?>>Secondary</option>
                                                            <option value="Tertiary" <?=$edu['level']=='Tertiary'?'selected':''?>>Tertiary</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">School Name</label>
                                                        <input type="text" name="school_name" class="form-control" value="<?=htmlspecialchars($edu['school_name'])?>" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Course</label>
                                                        <input type="text" name="course" class="form-control" value="<?=htmlspecialchars($edu['course']??'')?>">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Address</label>
                                                        <input type="text" name="address" class="form-control" value="<?=htmlspecialchars($edu['address']??'')?>">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label">Start Year</label>
                                                        <input type="number" name="start_year" min="1900" max="2099" class="form-control" value="<?=htmlspecialchars($edu['start_year'])?>" required>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label">End Year</label>
                                                        <input type="number" name="end_year" min="1900" max="2099" class="form-control" value="<?=htmlspecialchars($edu['end_year'])?>" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Image</label>
                                                        <?php if(!empty($edu['image_path'])): ?><div class="mb-2"><img src="<?=htmlspecialchars($edu['image_path'])?>" class="edu-img" alt=""></div><?php endif; ?>
                                                        <div class="upload-zone"><input type="file" name="education_image" accept="image/*"><i class="fas fa-school"></i><p><strong>Upload new image</strong> to replace</p></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn-outline-navy" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" name="update_education" class="btn-save"><i class="fas fa-floppy-disk"></i> Save Changes</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div><!-- /education tab -->

    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script>
const menuToggle=document.getElementById('menuToggle'),sidebar=document.getElementById('sidebar');
if(menuToggle&&sidebar)menuToggle.addEventListener('click',()=>sidebar.classList.toggle('active'));

// Custom tab switching
document.querySelectorAll('.tab-btn').forEach(btn=>{
    btn.addEventListener('click',()=>{
        document.querySelectorAll('.tab-btn').forEach(b=>b.classList.remove('active'));
        document.querySelectorAll('.tab-pane').forEach(p=>p.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById(btn.dataset.tab).classList.add('active');
    });
});

// Activate tab from URL hash
const hash=window.location.hash.replace('#','');
if(hash==='education'){document.querySelector('[data-tab="education"]').click();}

// File input labels
document.querySelectorAll('.upload-zone input[type="file"]').forEach(i=>{
    i.addEventListener('change',function(){const l=this.closest('.upload-zone').querySelector('p strong');if(l&&this.files.length)l.textContent=this.files.length>1?this.files.length+' files selected':this.files[0].name;});
});
</script>
</body></html>