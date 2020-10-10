import React, { useEffect, useState } from "react";
import UserTableMenu from "./UserTableMenu";
import UserTableView from "./UserTableView";
import { connect } from "react-redux";
import { Segment } from "semantic-ui-react";
import ServiceClient from "./../../modules/ServiceClient";
import { loadUsers, removeUserFromList } from "./../../store/Actions";

const UsersManager = ({
  currentUser,
  loadUsers,
  isLoaded,
  users,
  removeUserFromList,
}) => {
  const [loading, setLoadingState] = useState(false);

  useEffect(() => {
    if (loading) return;

    const serviceClient = new ServiceClient();
    if (!isLoaded) {
      setLoadingState(true);
      serviceClient.getUsers(currentUser.team).then((data) => {
        setLoadingState(false);
        loadUsers(data);
      });
    }
  }, [isLoaded, loading]);

  return (
    <Segment basic loading={!isLoaded}>
      <UserTableMenu />
      <UserTableView
        users={users}
        currentUser={currentUser}
        removeUserFromList={removeUserFromList}
      />
    </Segment>
  );
};

export default connect(
  (state) => ({
    isLoaded: state.users.isLoaded,
    users: state.users.list,
    currentUser: state.user.currentUser,
  }),
  { loadUsers, removeUserFromList }
)(UsersManager);
