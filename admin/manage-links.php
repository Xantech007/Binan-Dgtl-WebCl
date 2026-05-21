<?php

require_once __DIR__ . '/inc/header.php';
require_once __DIR__ . '/inc/countries.php';

$message = '';
$error = '';

/* LOAD VIP PLANS */
$vipStmt = $pdo->query("
    SELECT name 
    FROM vip 
    ORDER BY id ASC
");

$vipPlans = $vipStmt->fetchAll(PDO::FETCH_ASSOC);

/* ================= HANDLE FORM ================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';

    try {

        $country = trim($_POST['country'] ?? '');
        $vip_name = trim($_POST['vip_name'] ?? '');
        $link_address = trim($_POST['link_address'] ?? '');

        if (empty($country)) {
            throw new Exception("Country is required");
        }

        if (empty($vip_name)) {
            throw new Exception("VIP plan is required");
        }

        if (empty($link_address)) {
            throw new Exception("Link address is required");
        }

        /* ================= ADD ================= */

        if ($action === "add") {

            $stmt = $pdo->prepare("
                INSERT INTO links
                (country, vip_name, link_address)
                VALUES (?, ?, ?)
            ");

            $stmt->execute([
                $country,
                $vip_name,
                $link_address
            ]);

            $message = "Link added successfully";
        }

        /* ================= EDIT ================= */

        if ($action === "edit") {

            $id = (int)($_POST['id'] ?? 0);

            $stmt = $pdo->prepare("
                UPDATE links SET
                country = ?,
                vip_name = ?,
                link_address = ?
                WHERE id = ?
            ");

            $stmt->execute([
                $country,
                $vip_name,
                $link_address,
                $id
            ]);

            $message = "Link updated successfully";
        }

    } catch (Exception $e) {

        $error = $e->getMessage();

    }

}

/* ================= DELETE ================= */

if (
    isset($_POST['action']) &&
    $_POST['action'] === "delete"
) {

    $id = (int)($_POST['id'] ?? 0);

    $stmt = $pdo->prepare("
        DELETE FROM links
        WHERE id = ?
    ");

    $stmt->execute([$id]);

    $message = "Link deleted successfully";
}

/* ================= LOAD LINKS ================= */

$stmt = $pdo->query("
    SELECT *
    FROM links
    ORDER BY id DESC
");

$links = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<main>

<h1 style="text-align:center;margin:2.5rem 0 2rem;">
    Manage Paystack Links
</h1>

<?php if ($message): ?>

<div style="
background:#238636;
color:white;
padding:1.2rem;
border-radius:8px;
margin-bottom:2rem;
text-align:center;
max-width:900px;
margin-left:auto;
margin-right:auto;
">

    <?= htmlspecialchars($message) ?>

</div>

<?php endif; ?>

<?php if ($error): ?>

<div style="
background:#f85149;
color:white;
padding:1.2rem;
border-radius:8px;
margin-bottom:2rem;
text-align:center;
max-width:900px;
margin-left:auto;
margin-right:auto;
">

    <?= htmlspecialchars($error) ?>

</div>

<?php endif; ?>

<!-- ================= ADD LINK ================= -->

<div style="
background:var(--card);
border:1px solid var(--border);
border-radius:12px;
padding:2rem;
margin-bottom:3rem;
max-width:900px;
margin-left:auto;
margin-right:auto;
">

<h2 style="margin-bottom:1.8rem;text-align:center;">
    Add Link
</h2>

<form method="POST">

<input type="hidden" name="action" value="add">

<!-- COUNTRY -->

<div style="margin-bottom:1.4rem;">

<label>Country</label>

<select name="country"
style="width:100%;padding:0.8rem;"
required>

<option value="">
    Select Country
</option>

<?php foreach ($countries as $country): ?>

<option value="<?= htmlspecialchars($country) ?>">

    <?= htmlspecialchars($country) ?>

</option>

<?php endforeach; ?>

</select>

</div>

<!-- VIP -->

<div style="margin-bottom:1.4rem;">

<label>VIP Plan</label>

<select name="vip_name"
style="width:100%;padding:0.8rem;"
required>

<option value="">
    Select VIP Plan
</option>

<?php foreach ($vipPlans as $vip): ?>

<option value="<?= htmlspecialchars($vip['name']) ?>">

    <?= htmlspecialchars($vip['name']) ?>

</option>

<?php endforeach; ?>

</select>

</div>

<!-- LINK -->

<div style="margin-bottom:2rem;">

<label>Link Address</label>

<input type="url"
name="link_address"
placeholder="https://example.com/pay"
required
style="width:100%;padding:0.8rem;">

</div>

<button type="submit"
class="btn"
style="width:100%;padding:1rem;">

    Add Link

</button>

</form>

</div>

<!-- ================= LIST LINKS ================= -->

<h2 style="text-align:center;margin:3rem 0 1.5rem;">
    Existing Links
</h2>

<div style="overflow-x:auto;">

<table style="
width:100%;
max-width:1100px;
margin:0 auto;
border-collapse:separate;
border-spacing:0 10px;
">

<thead>

<tr style="background:#1f2937;">

<th>ID</th>
<th>Country</th>
<th>VIP Name</th>
<th>Link Address</th>
<th>Actions</th>

</tr>

</thead>

<tbody>

<?php foreach ($links as $link): ?>

<tr style="background:var(--card);">

<td style="padding:1rem;text-align:center">
    <?= $link['id'] ?>
</td>

<td style="padding:1rem;text-align:center">
    <?= htmlspecialchars($link['country']) ?>
</td>

<td style="padding:1rem;text-align:center">
    <?= htmlspecialchars($link['vip_name']) ?>
</td>

<td style="padding:1rem;max-width:350px;word-break:break-all;">
    <?= htmlspecialchars($link['link_address']) ?>
</td>

<td style="padding:1rem;text-align:center;">

<button
class="btn"
style="margin-right:6px"
onclick='openEditModal(<?= json_encode($link, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>

    Edit

</button>

<form method="POST" style="display:inline;">

<input type="hidden" name="action" value="delete">

<input type="hidden" name="id" value="<?= $link['id'] ?>">

<button class="btn red">
    Delete
</button>

</form>

</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>

<!-- ================= EDIT MODAL ================= -->

<div id="editModal"
style="
display:none;
position:fixed;
inset:0;
background:rgba(0,0,0,.7);
align-items:center;
justify-content:center;
z-index:9999;
overflow-y:auto;
padding:20px;
">

<div style="
background:var(--card);
border:1px solid var(--border);
border-radius:12px;
width:90%;
max-width:700px;
padding:2rem;
position:relative;
">

<button
onclick="closeEditModal()"
style="
position:absolute;
right:15px;
top:10px;
font-size:22px;
background:none;
border:none;
color:white;
cursor:pointer;
">

×

</button>

<h2 style="text-align:center;margin-bottom:1.5rem;">
    Edit Link
</h2>

<form method="POST">

<input type="hidden" name="action" value="edit">

<input type="hidden" name="id" id="edit_id">

<!-- COUNTRY -->

<div style="margin-bottom:1.4rem;">

<label>Country</label>

<select name="country"
id="edit_country"
style="width:100%;padding:0.8rem;"
required>

<option value="">
    Select Country
</option>

<?php foreach ($countries as $country): ?>

<option value="<?= htmlspecialchars($country) ?>">

    <?= htmlspecialchars($country) ?>

</option>

<?php endforeach; ?>

</select>

</div>

<!-- VIP -->

<div style="margin-bottom:1.4rem;">

<label>VIP Plan</label>

<select name="vip_name"
id="edit_vip_name"
style="width:100%;padding:0.8rem;"
required>

<option value="">
    Select VIP Plan
</option>

<?php foreach ($vipPlans as $vip): ?>

<option value="<?= htmlspecialchars($vip['name']) ?>">

    <?= htmlspecialchars($vip['name']) ?>

</option>

<?php endforeach; ?>

</select>

</div>

<!-- LINK -->

<div style="margin-bottom:2rem;">

<label>Link Address</label>

<input type="url"
name="link_address"
id="edit_link_address"
required
style="width:100%;padding:0.8rem;">

</div>

<button class="btn"
style="width:100%;padding:1rem;">

    Save Changes

</button>

</form>

</div>

</div>

</main>

<script>

function openEditModal(link){

document.getElementById("editModal").style.display = "flex";

document.getElementById("edit_id").value = link.id;

document.getElementById("edit_country").value = link.country || "";

document.getElementById("edit_vip_name").value = link.vip_name || "";

document.getElementById("edit_link_address").value = link.link_address || "";

}

function closeEditModal(){

document.getElementById("editModal").style.display = "none";

}

</script>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
