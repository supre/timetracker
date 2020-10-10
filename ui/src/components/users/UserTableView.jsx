import React, { useState } from "react";
import { Table, Button, Modal } from "semantic-ui-react";
import ServiceClient from "./../../modules/ServiceClient";
import UserModal from "./UserModal";
import { NavLink } from "react-router-dom";
import ChangePassword from "../navbar/ChangePassword";

export default ({ users, currentUser, removeUserFromList }) => {
  const DeleteModal = ({ user, isOpen, closeModal }) => {
    const [loading, setLoading] = useState(false);

    const deleteUser = () => {
      setLoading(true);
      new ServiceClient().deleteUser(currentUser.team, user.id).then(() => {
        removeUserFromList(user);
        setLoading(false);
        closeModal();
      });
    };

    return (
      <Modal open={isOpen} onClose={closeModal}>
        <Modal.Header>Delete Your Account</Modal.Header>
        <Modal.Content>
          <p>Are you sure you want to delete this user</p>
        </Modal.Content>
        <Modal.Actions>
          <Button
            loading={loading}
            disabled={loading}
            positive
            icon="checkmark"
            labelPosition="right"
            content="Yes"
            onClick={() => deleteUser()}
          />
        </Modal.Actions>
      </Modal>
    );
  };

  const canCurrentUserDeleteThisUser = (user) => {
    // FIXME we have the same logic on backend. We should have it at only one place.
    // Backend should be guiding front end here.
    if (currentUser.role === "manager" && user.role === "manager") return false;
    if (user.role === "admin") return false;
    return true;
  };

  const canCurrentUserEditThisUser = (user) => {
    if (currentUser.role === "manager" && user.role === "manager") return false;
    if (user.role === "admin") return false;
    return true;
  };

  const UserRow = ({ user }) => {
    const [isEditModalOpen, setEditModalState] = useState(false);
    const [isDeleteModalOpen, setDeleteModalState] = useState(false);
    const [isPasswordModalOpen, setPasswordModalState] = useState(false);

    return (
      <Table.Row>
        <Table.Cell>{user.displayName}</Table.Cell>
        <Table.Cell>{user.email}</Table.Cell>
        <Table.Cell>{user.role}</Table.Cell>
        <Table.Cell>
          {currentUser.role === "admin" && (
            <Button
              as={NavLink}
              to={"/users/" + user.id + "/entries"}
              circular
              icon="bars"
              basic
            />
          )}
          <UserModal
            action="update"
            id={user.id}
            displayName={user.displayName}
            email={user.email}
            role={user.role}
            userModalOpenState={isEditModalOpen}
            onUserModelClose={() => setEditModalState(false)}
          />
          <Button
            circular
            icon="edit"
            onClick={() => setEditModalState(true)}
            basic
            disabled={!canCurrentUserEditThisUser(user)}
          />

          <ChangePassword
            open={isPasswordModalOpen}
            onModalClose={() => setPasswordModalState(false)}
            team={user.team}
            userId={user.id}
          />

          <Button
            circular
            icon="lock"
            onClick={() => setPasswordModalState(true)}
            basic
            disabled={!canCurrentUserEditThisUser(user)}
          />

          <DeleteModal
            user={user}
            isOpen={isDeleteModalOpen}
            closeModal={() => setDeleteModalState(false)}
          />
          <Button
            circular
            icon="delete"
            basic
            disabled={!canCurrentUserDeleteThisUser(user)}
            onClick={() => setDeleteModalState(true)}
          />
        </Table.Cell>
      </Table.Row>
    );
  };

  const loadUsers = () => {
    return users.map((user) => <UserRow key={user.id} user={user} />);
  };

  return (
    <Table attached="bottom" columns={4}>
      <Table.Header>
        <Table.Row>
          <Table.HeaderCell>Display Name</Table.HeaderCell>
          <Table.HeaderCell>Email</Table.HeaderCell>
          <Table.HeaderCell>Role</Table.HeaderCell>
          <Table.HeaderCell>Actions</Table.HeaderCell>
        </Table.Row>
      </Table.Header>
      <Table.Body>{loadUsers()}</Table.Body>
    </Table>
  );
};
