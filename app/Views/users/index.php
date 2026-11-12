<?php if ($message): ?>
<div class="card" style="margin-bottom:1rem; border-left:4px solid <?= $messageType === 'error' ? 'var(--expense)' : 'var(--income)' ?>;">
    <?= e($message) ?>
</div>
<?php endif; ?>

<section class="card" style="margin-bottom:1rem;">
    <div style="display:flex; align-items:center; justify-content:space-between; gap:1rem; flex-wrap:wrap; margin-bottom:.5rem;">
        <h2 style="margin:0;"><?= e(t('employees_table')) ?></h2>
        <button type="button" class="btn btn-primary" id="btnAddEmployee">+ <?= e(t('add_employee')) ?></button>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th><?= e(t('full_name')) ?></th>
                    <th><?= e(t('birth_date')) ?></th>
                    <th><?= e(t('hire_date')) ?></th>
                    <th><?= e(t('termination_date')) ?></th>
                    <th><?= e(t('position')) ?></th>
                    <th><?= e(t('system_user')) ?></th>
                    <th><?= e(t('actions')) ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($employees)): foreach ($employees as $emp): $eid=(int)$emp['id']; $posKey='pos_'.(string)$emp['position']; ?>
                <tr>
                    <td><?= e($emp['full_name'] ?? '') ?></td>
                    <td><?= e($emp['birth_date'] ?? '—') ?></td>
                    <td><?= e($emp['hire_date'] ?? '—') ?></td>
                    <td><?= !empty($emp['termination_date']) ? e($emp['termination_date']) : e(t('present_time')) ?></td>
                    <td><?= e(t($posKey)) ?></td>
                    <td><?= !empty($emp['user_username']) ? e($emp['user_username']) : e(t('no_system_user')) ?></td>
                    <td>
                        <div class="actions-cell">
                            <button type="button" class="icon-btn btn-edit-employee" title="<?= e(t('edit')) ?>" aria-label="<?= e(t('edit')) ?>"
                                data-id="<?= $eid ?>"
                                data-full-name="<?= e($emp['full_name'] ?? '') ?>"
                                data-birth-date="<?= e($emp['birth_date'] ?? '') ?>"
                                data-hire-date="<?= e($emp['hire_date'] ?? '') ?>"
                                data-termination-date="<?= e($emp['termination_date'] ?? '') ?>"
                                data-position="<?= e($emp['position'] ?? 'seller') ?>"
                                data-user-id="<?= e($emp['user_id'] ?? '') ?>">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                            </button>
                            <form method="POST" action="<?= url('users') ?>" class="inline-form" onsubmit="return confirm(<?= e(json_encode(t('delete_employee_confirm'))) ?>)">
                                <input type="hidden" name="action" value="delete_employee">
                                <input type="hidden" name="id" value="<?= $eid ?>">
                                <button type="submit" class="icon-btn icon-btn-danger" title="<?= e(t('delete')) ?>" aria-label="<?= e(t('delete')) ?>">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; else: ?>
                <tr><td colspan="7" style="text-align:center; color: var(--muted); padding: 2rem;"><?= e(t('no_employees')) ?></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<section class="card">
    <div style="display:flex; align-items:center; justify-content:space-between; gap:1rem; flex-wrap:wrap; margin-bottom:.5rem;">
        <h2 style="margin:0;"><?= e(t('system_accounts')) ?></h2>
        <button type="button" class="btn btn-primary" id="btnAddUser">+ <?= e(t('add_user')) ?></button>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th><?= e(t('id')) ?></th><th><?= e(t('username')) ?></th><th><?= e(t('role')) ?></th><th><?= e(t('actions')) ?></th></tr></thead>
            <tbody>
                <?php if ($users): foreach ($users as $u): $roleLabel = ($u['role'] === 'admin') ? t('role_admin') : t('role_shop'); $uid=(int)$u['id']; ?>
                <tr>
                    <td><?= $uid ?></td>
                    <td><?= e($u['username']) ?></td>
                    <td><?= e($roleLabel) ?></td>
                    <td>
                        <div class="actions-cell">
                            <button type="button" class="icon-btn btn-activity-user" data-user-id="<?= $uid ?>" title="<?= e(t('activity')) ?>" aria-label="<?= e(t('activity')) ?>">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 12h4l2.5-6 5 12 2.5-6H21"/></svg>
                            </button>
                            <button type="button" class="icon-btn btn-edit-user" data-id="<?= $uid ?>" data-username="<?= e($u['username']) ?>" data-role="<?= e($u['role']) ?>" title="<?= e(t('edit')) ?>" aria-label="<?= e(t('edit')) ?>">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                            </button>
                            <?php if ($uid !== $selfId): ?>
                            <form method="POST" action="<?= url('users') ?>" class="inline-form" onsubmit="return confirm(<?= e(json_encode(t('delete_user_confirm'))) ?>)">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $uid ?>">
                                <button type="submit" class="icon-btn icon-btn-danger" title="<?= e(t('delete')) ?>" aria-label="<?= e(t('delete')) ?>">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/></svg>
                                </button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; else: ?>
                <tr><td colspan="4" style="text-align:center; color: var(--muted); padding: 2rem;"><?= e(t('no_users')) ?></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<!-- Employee modal -->
<div class="modal-backdrop" id="employeeModal" aria-hidden="true">
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="employeeModalTitle">
        <form method="POST" action="<?= url('users') ?>" id="employeeForm">
            <div class="modal-head">
                <h3 id="employeeModalTitle"><?= e(t('add_employee')) ?></h3>
                <button type="button" class="modal-close" id="employeeModalClose" aria-label="<?= e(t('close')) ?>">×</button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="action" id="employeeAction" value="create_employee">
                <input type="hidden" name="id" id="employeeId" value="">
                <div class="form-grid">
                    <div class="form-group" style="grid-column:1/-1;">
                        <label for="empFullName"><?= e(t('full_name')) ?></label>
                        <input type="text" id="empFullName" name="full_name" required>
                    </div>
                    <div class="form-group">
                        <label for="empBirthDate"><?= e(t('birth_date')) ?></label>
                        <input type="date" id="empBirthDate" name="birth_date">
                    </div>
                    <div class="form-group">
                        <label for="empPosition"><?= e(t('position')) ?></label>
                        <select id="empPosition" name="position" required>
                            <?php foreach (($positions ?? []) as $pos): ?>
                            <option value="<?= e($pos) ?>"><?= e(t('pos_' . $pos)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="empHireDate"><?= e(t('hire_date')) ?></label>
                        <input type="date" id="empHireDate" name="hire_date">
                    </div>
                    <div class="form-group">
                        <label for="empTermDate"><?= e(t('termination_date')) ?></label>
                        <input type="date" id="empTermDate" name="termination_date">
                        <label style="display:flex;align-items:center;gap:.4rem;margin-top:.35rem;font-weight:400;">
                            <input type="checkbox" id="empTermPresent" name="termination_present" value="1">
                            <?= e(t('present_time')) ?>
                        </label>
                    </div>
                    <div class="form-group" style="grid-column:1/-1;">
                        <label for="empUserId"><?= e(t('system_user')) ?></label>
                        <select id="empUserId" name="user_id">
                            <option value=""><?= e(t('no_system_user')) ?></option>
                            <?php foreach ($users as $u): $rl = ($u['role'] === 'admin') ? t('role_admin') : t('role_shop'); ?>
                            <option value="<?= (int)$u['id'] ?>"><?= e($u['username']) ?> (<?= e($rl) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn btn-outline" id="employeeModalCancel"><?= e(t('cancel')) ?></button>
                <button type="submit" class="btn btn-primary"><?= e(t('save')) ?></button>
            </div>
        </form>
    </div>
</div>

<div class="modal-backdrop" id="addUserModal" aria-hidden="true">
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="addUserModalTitle">
        <form method="POST" action="<?= url('users') ?>" id="addUserForm">
            <div class="modal-head">
                <h3 id="addUserModalTitle"><?= e(t('add_user_title')) ?></h3>
                <button type="button" class="modal-close" id="addUserModalClose" aria-label="<?= e(t('close')) ?>">×</button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="action" value="create">
                <div class="form-grid">
                    <div class="form-group" style="grid-column:1/-1;">
                        <label for="newUsername"><?= e(t('username')) ?></label>
                        <input type="text" id="newUsername" name="username" required autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label for="newPassword"><?= e(t('password')) ?></label>
                        <input type="password" id="newPassword" name="password" required autocomplete="new-password">
                    </div>
                    <div class="form-group">
                        <label for="newPasswordConfirm"><?= e(t('repeat_password')) ?></label>
                        <input type="password" id="newPasswordConfirm" name="password_confirm" required autocomplete="new-password">
                    </div>
                    <div class="form-group" style="grid-column:1/-1;">
                        <label for="newRole"><?= e(t('role')) ?></label>
                        <select id="newRole" name="role">
                            <option value="shop"><?= e(t('role_shop')) ?></option>
                            <option value="admin"><?= e(t('role_admin')) ?></option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn btn-outline" id="addUserModalCancel"><?= e(t('cancel')) ?></button>
                <button type="submit" class="btn btn-primary"><?= e(t('create')) ?></button>
            </div>
        </form>
    </div>
</div>

<div class="modal-backdrop" id="editUserModal" aria-hidden="true">
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="editUserModalTitle">
        <form method="POST" action="<?= url('users') ?>" id="editUserForm">
            <div class="modal-head">
                <h3 id="editUserModalTitle"><?= e(t('edit_user')) ?></h3>
                <button type="button" class="modal-close" id="editUserModalClose" aria-label="<?= e(t('close')) ?>">×</button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="editUserId" value="">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="editUsername"><?= e(t('username')) ?></label>
                        <input type="text" id="editUsername" name="username" required autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label for="editPassword"><?= e(t('new_password_optional')) ?></label>
                        <input type="password" id="editPassword" name="password" placeholder="<?= e(t('leave_blank_password')) ?>" autocomplete="new-password">
                    </div>
                    <div class="form-group" style="grid-column:1/-1;">
                        <label for="editRole"><?= e(t('role')) ?></label>
                        <select id="editRole" name="role">
                            <option value="shop"><?= e(t('role_shop')) ?></option>
                            <option value="admin"><?= e(t('role_admin')) ?></option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn btn-outline" id="editUserModalCancel"><?= e(t('cancel')) ?></button>
                <button type="submit" class="btn btn-primary"><?= e(t('save')) ?></button>
            </div>
        </form>
    </div>
</div>

<?php foreach ($users as $u): $uid=(int)$u['id']; $events = $userActivity[$uid] ?? []; $roleLabel = ($u['role'] === 'admin') ? t('role_admin') : t('role_shop'); $linkedEmp = $userEmployeeMap[$uid] ?? null; $linkedPosKey = $linkedEmp ? ('pos_' . (string)($linkedEmp['position'] ?? 'seller')) : ''; ?>
<div class="modal-backdrop" id="activityModal-<?= $uid ?>" aria-hidden="true">
    <div class="modal modal-wide" role="dialog" aria-modal="true" aria-labelledby="activityTitle-<?= $uid ?>">
        <div class="modal-head">
            <h3 id="activityTitle-<?= $uid ?>"><?= e(t('user_activity')) ?><?= e($u['username']) ?> <span style="color:var(--muted); font-weight:400; font-size:.9rem;">(<?= e($roleLabel) ?>)</span></h3>
            <button type="button" class="modal-close activity-close" data-user-id="<?= $uid ?>" aria-label="<?= e(t('close')) ?>">×</button>
        </div>
        <div class="modal-body">
            <div style="margin:0 0 1rem; padding:.75rem 1rem; border:1px solid var(--border); border-radius:10px; background:var(--surface); display:grid; gap:.35rem;">
                <div><strong><?= e(t('username')) ?>:</strong> <?= e($u['username']) ?></div>
                <div><strong><?= e(t('employee')) ?>:</strong>
                    <?php if ($linkedEmp): ?>
                        <?= e((string)($linkedEmp['full_name'] ?? '')) ?><?php if ($linkedPosKey !== ''): ?> <span style="color:var(--muted);">(<?= e(t($linkedPosKey)) ?>)</span><?php endif; ?>
                    <?php else: ?>
                        <span style="color:var(--muted);"><?= e(t('not_linked')) ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <p style="color:var(--muted); margin:0 0 1rem; font-size:.9rem;"><?= e(t('activity_hint')) ?></p>
            <?php if ($events): ?>
            <ul class="activity-list">
                <?php foreach ($events as $ev):
                    $etype = (string) ($ev['type'] ?? '');
                    $badge = $etype === 'login' ? t('event_login') : ($etype === 'logout' ? t('event_logout') : ($etype === 'sale' ? t('event_sale') : t('event_stock_in')));
                ?>
                <li class="activity-item">
                    <div class="activity-time"><?= e((string) ($ev['at'] ?? '—')) ?></div>
                    <div class="activity-title"><span class="badge-act <?= e($etype) ?>"><?= e($badge) ?></span></div>
                    <div class="activity-detail"><strong><?= e((string) ($ev['title'] ?? '')) ?></strong><?php if (!empty($ev['detail'])): ?><br><?= e((string) $ev['detail']) ?><?php endif; ?></div>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php else: ?>
            <p style="color:var(--muted); margin:0;"><?= e(t('no_activity')) ?></p>
            <?php endif; ?>
        </div>
        <div class="modal-foot">
            <button type="button" class="btn btn-outline activity-ok" data-user-id="<?= $uid ?>"><?= e(t('close')) ?></button>
        </div>
    </div>
</div>
<?php endforeach; ?>

<style>
    .modal-backdrop { display:none; position:fixed; inset:0; background:var(--overlay); z-index:1000; align-items:center; justify-content:center; padding:1rem; backdrop-filter:blur(2px); }
    .modal-backdrop.open { display:flex; }
    .modal { width:100%; max-width:560px; background:var(--card); color:var(--text); border:1px solid var(--border); border-radius:12px; box-shadow:var(--shadow-lg, var(--shadow)); overflow:hidden; }
    .modal.modal-wide { max-width: 900px; }
    .modal-head { display:flex; align-items:center; justify-content:space-between; padding:1rem 1.25rem; border-bottom:1px solid var(--border); background:var(--card); }
    .modal-head h3 { margin:0; font-size:1.1rem; color:var(--text); }
    .modal-close { display:inline-flex; align-items:center; justify-content:center; padding:0; appearance:none; -webkit-appearance:none; background:var(--surface); border:1px solid var(--border); color:var(--muted); width:32px; height:32px; border-radius:8px; cursor:pointer; font-size:1.1rem; line-height:1; }
    .modal-close:hover { color:var(--text); border-color:var(--accent); }
    .modal-body { padding:1.25rem; background:var(--card); }
    .modal-foot { display:flex; gap:.75rem; justify-content:flex-end; padding:1rem 1.25rem; border-top:1px solid var(--border); background:var(--card); }
    .modal .form-group label { color:var(--muted); font-size:.9rem; }
    .modal .form-group input, .modal .form-group select { width:100%; padding:.55rem .65rem; background:var(--surface); border:1px solid var(--border); border-radius:8px; color:var(--text); font:inherit; box-sizing:border-box; }
    .actions-cell { display:inline-flex; gap:.4rem; flex-wrap:nowrap; align-items:center; justify-content:flex-start; }
    .inline-form { display:inline-flex; margin:0; }
    .icon-btn { width:34px; height:34px; display:inline-flex; align-items:center; justify-content:center; border-radius:9px; border:1px solid var(--border); background:var(--surface); color:var(--muted); cursor:pointer; transition:all .16s ease; padding:0; line-height:1; flex:0 0 auto; }
    .icon-btn svg { width:17px; height:17px; display:block; }
    .icon-btn:hover { color:var(--accent); border-color:rgba(56,189,248,.45); background:rgba(56,189,248,.08); transform:translateY(-1px); }
    .icon-btn:active { transform:translateY(0); }
    .icon-btn:focus-visible { outline:2px solid var(--accent); outline-offset:2px; }
    .icon-btn-danger:hover { color:var(--expense); border-color:rgba(239,68,68,.45); background:rgba(239,68,68,.08); }
    .activity-list { list-style:none; padding:0; margin:0; max-height:60vh; overflow:auto; display:flex; flex-direction:column; gap:.6rem; }
    .activity-item { display:grid; grid-template-columns: 170px 180px 1fr; gap:.5rem 1rem; align-items:start; padding:.65rem .75rem; border:1px solid var(--border); border-radius:10px; background:var(--surface); }
    .activity-time { color:var(--muted); font-size:.85rem; white-space:nowrap; }
    .activity-title { font-weight:600; }
    .activity-detail { color:var(--muted); font-size:.9rem; }
    .badge-act { display:inline-block; padding:.15rem .5rem; border-radius:999px; font-size:.8rem; font-weight:600; }
    .badge-act.login { color:var(--income); background:rgba(34,197,94,.12); border:1px solid rgba(34,197,94,.25); }
    .badge-act.logout { color:var(--muted); background:rgba(148,163,184,.12); border:1px solid var(--border); }
    .badge-act.sale { color:var(--expense); background:rgba(239,68,68,.12); border:1px solid rgba(239,68,68,.25); }
    .badge-act.stock_in { color:var(--accent); background:rgba(56,189,248,.12); border:1px solid rgba(56,189,248,.25); }
    @media (max-width: 720px) { .activity-item { grid-template-columns: 1fr; } .modal.modal-wide { max-width: 100%; } }
</style>
<script>
(function(){
  const addModal=document.getElementById('addUserModal'); const addOpen=document.getElementById('btnAddUser'); const addClose=document.getElementById('addUserModalClose'); const addCancel=document.getElementById('addUserModalCancel'); const addFirst=document.getElementById('newUsername');
  function openAdd(){ if(!addModal) return; addModal.classList.add('open'); addModal.setAttribute('aria-hidden','false'); if(addFirst) addFirst.focus(); }
  function closeAdd(){ if(!addModal) return; addModal.classList.remove('open'); addModal.setAttribute('aria-hidden','true'); if(addOpen) addOpen.focus(); }
  if(addOpen) addOpen.addEventListener('click', openAdd); if(addClose) addClose.addEventListener('click', closeAdd); if(addCancel) addCancel.addEventListener('click', closeAdd); if(addModal) addModal.addEventListener('click', e=>{ if(e.target===addModal) closeAdd(); });

  const editModal=document.getElementById('editUserModal'); const editClose=document.getElementById('editUserModalClose'); const editCancel=document.getElementById('editUserModalCancel'); const editId=document.getElementById('editUserId'); const editUsername=document.getElementById('editUsername'); const editRole=document.getElementById('editRole'); const editPassword=document.getElementById('editPassword'); let lastEditBtn=null;
  function openEdit(id, username, role, btn){ if(!editModal) return; lastEditBtn=btn||null; editId.value=id||''; editUsername.value=username||''; editRole.value=(role==='admin')?'admin':'shop'; if(editPassword) editPassword.value=''; editModal.classList.add('open'); editModal.setAttribute('aria-hidden','false'); editUsername.focus(); }
  function closeEdit(){ if(!editModal) return; editModal.classList.remove('open'); editModal.setAttribute('aria-hidden','true'); if(lastEditBtn) lastEditBtn.focus(); }
  document.querySelectorAll('.btn-edit-user').forEach(btn=>btn.addEventListener('click', ()=>openEdit(btn.dataset.id, btn.dataset.username, btn.dataset.role, btn)));
  if(editClose) editClose.addEventListener('click', closeEdit); if(editCancel) editCancel.addEventListener('click', closeEdit); if(editModal) editModal.addEventListener('click', e=>{ if(e.target===editModal) closeEdit(); });

  const empModal=document.getElementById('employeeModal'); const empOpen=document.getElementById('btnAddEmployee'); const empClose=document.getElementById('employeeModalClose'); const empCancel=document.getElementById('employeeModalCancel');
  const empTitle=document.getElementById('employeeModalTitle'); const empAction=document.getElementById('employeeAction'); const empId=document.getElementById('employeeId');
  const empName=document.getElementById('empFullName'); const empBirth=document.getElementById('empBirthDate'); const empPos=document.getElementById('empPosition');
  const empHire=document.getElementById('empHireDate'); const empTerm=document.getElementById('empTermDate'); const empTermPresent=document.getElementById('empTermPresent'); const empUser=document.getElementById('empUserId'); let lastEmpBtn=null;
  const T_ADD = <?= json_encode(t('add_employee')) ?>; const T_EDIT = <?= json_encode(t('edit_employee')) ?>;
  function syncTerm(){ if(!empTerm||!empTermPresent) return; empTerm.disabled = empTermPresent.checked; if(empTermPresent.checked) empTerm.value=''; }
  if(empTermPresent) empTermPresent.addEventListener('change', syncTerm);
  function openEmp(mode, data, btn){
    if(!empModal) return; lastEmpBtn=btn||null; data=data||{};
    empAction.value = (mode==='edit') ? 'update_employee' : 'create_employee';
    empId.value = data.id || '';
    empName.value = data.fullName || '';
    if(empBirth) empBirth.value = data.birthDate || '';
    empPos.value = data.position || 'seller';
    empHire.value = data.hireDate || '';
    const hasTerm = !!(data.termDate);
    if(empTermPresent) empTermPresent.checked = !hasTerm;
    if(empTerm) empTerm.value = data.termDate || '';
    syncTerm();
    empUser.value = data.userId || '';
    if(empTitle) empTitle.textContent = (mode==='edit') ? T_EDIT : T_ADD;
    empModal.classList.add('open'); empModal.setAttribute('aria-hidden','false'); if(empName) empName.focus();
  }
  function closeEmp(){ if(!empModal) return; empModal.classList.remove('open'); empModal.setAttribute('aria-hidden','true'); if(lastEmpBtn) lastEmpBtn.focus(); else if(empOpen) empOpen.focus(); }
  if(empOpen) empOpen.addEventListener('click', ()=>openEmp('create', {}, empOpen));
  document.querySelectorAll('.btn-edit-employee').forEach(btn=>btn.addEventListener('click', ()=>openEmp('edit', {
    id: btn.dataset.id, fullName: btn.dataset.fullName, birthDate: btn.dataset.birthDate,
    hireDate: btn.dataset.hireDate, termDate: btn.dataset.terminationDate, position: btn.dataset.position, userId: btn.dataset.userId
  }, btn)));
  if(empClose) empClose.addEventListener('click', closeEmp); if(empCancel) empCancel.addEventListener('click', closeEmp); if(empModal) empModal.addEventListener('click', e=>{ if(e.target===empModal) closeEmp(); });

  let lastActivityBtn=null;
  function openActivity(uid, btn){ const m=document.getElementById('activityModal-'+uid); if(!m) return; lastActivityBtn=btn||null; m.classList.add('open'); m.setAttribute('aria-hidden','false'); const c=m.querySelector('.activity-close'); if(c) c.focus(); }
  function closeActivity(uid){ const m=document.getElementById('activityModal-'+uid); if(!m) return; m.classList.remove('open'); m.setAttribute('aria-hidden','true'); if(lastActivityBtn) lastActivityBtn.focus(); }
  document.querySelectorAll('.btn-activity-user').forEach(btn=>btn.addEventListener('click', ()=>openActivity(btn.dataset.userId, btn)));
  document.querySelectorAll('.activity-close').forEach(btn=>btn.addEventListener('click', ()=>closeActivity(btn.dataset.userId)));
  document.querySelectorAll('.activity-ok').forEach(btn=>btn.addEventListener('click', ()=>closeActivity(btn.dataset.userId)));
  document.querySelectorAll('[id^="activityModal-"]').forEach(m=>m.addEventListener('click', e=>{ if(e.target===m){ closeActivity(m.id.replace('activityModal-','')); } }));

  document.addEventListener('keydown', e=>{
    if(e.key!=='Escape') return;
    if(empModal && empModal.classList.contains('open')) return closeEmp();
    if(editModal && editModal.classList.contains('open')) return closeEdit();
    if(addModal && addModal.classList.contains('open')) return closeAdd();
    const openAct = document.querySelector('[id^="activityModal-"].open');
    if(openAct){ closeActivity(openAct.id.replace('activityModal-','')); }
  });
  <?php if ($createReopen): ?>openAdd();<?php endif; ?>
  <?php if ($editReopen): ?>openEdit(<?= json_encode((string)$editReopen) ?>, <?= json_encode($editReopenUsername) ?>, <?= json_encode($editReopenRole) ?>, null);<?php endif; ?>
  <?php if (!empty($editEmpReopen)): ?>openEmp(<?= json_encode(($empForm['action'] ?? '') === 'update_employee' ? 'edit' : 'create') ?>, <?= json_encode(['id'=>(string)($empForm['id']??''),'fullName'=>(string)($empForm['full_name']??''),'birthDate'=>(string)($empForm['birth_date']??''),'hireDate'=>(string)($empForm['hire_date']??''),'termDate'=>(string)($empForm['termination_date']??''),'position'=>(string)($empForm['position']??'seller'),'userId'=>(string)($empForm['user_id']??'')]) ?>, null);<?php if (!empty($empForm['termination_present'])): ?>if(typeof syncTerm==='function'&&empTermPresent){empTermPresent.checked=true;syncTerm();}<?php endif; ?><?php endif; ?>
})();
</script>
