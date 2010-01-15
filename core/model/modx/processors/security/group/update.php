<?php
/**
 * Update a user group
 *
 * @param integer $id The ID of the user group
 * @param string $name The new name of the user group
 *
 * @package modx
 * @subpackage processors.security.group
 */
if (!$modx->hasPermission('access_permissions')) return $modx->error->failure($modx->lexicon('permission_denied'));
$modx->lexicon->load('user');

/* get usergroup */
if (empty($_POST['id'])) {
    $usergroup = $modx->newObject('modUserGroup');
    $usergroup->set('id',0);
} else {
    $usergroup = $modx->getObject('modUserGroup',$_POST['id']);
    if ($usergroup == null) return $modx->error->failure($modx->lexicon('user_group_err_not_found'));
}

/* set fields */
$usergroup->fromArray($_POST);

/* users */
if (isset($_POST['users']) && !empty($_POST['id'])) {
    $ous = $usergroup->getMany('UserGroupMembers');
    foreach ($ous as $ou) { $ou->remove(); }
    $users = $modx->fromJSON($_POST['users']);
    foreach ($users as $user) {
        $member = $modx->newObject('modUserGroupMember');
        $member->set('user_group',$usergroup->get('id'));
        $member->set('member',$user['id']);
        $member->set('role',empty($user['role']) ? 0 : $user['role']);

        $member->save();
    }
}

/* save usergroup if not anonymous */
if (!empty($_POST['id'])) {
    if ($usergroup->save() === false) {
        return $modx->error->failure($modx->lexicon('user_group_err_save'));
    }
}

/* log manager action */
$modx->logManagerAction('save_user_group','modUserGroup',$usergroup->get('id'));

return $modx->error->success('',$usergroup);