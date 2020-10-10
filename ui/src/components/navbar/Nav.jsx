import React from "react";
import { Menu, Divider } from "semantic-ui-react";

import UserPanel from "./UserPanel";
import AdminPanel from "./AdminPanel";

export default ({ currentUser, logOut }) => {
  const isCurrentUserAdmin = currentUser.role === "admin";
  const isCurrentUserManager = currentUser.role === "manager";
  const hasAccessToUserManager = isCurrentUserAdmin || isCurrentUserManager;

  return (
    <Menu
      size="large"
      inverted
      fixed="left"
      vertical
      style={{ fontSize: "1.2rem", backgroundColor: "#2d3a7a" }}
      color="black"
    >
      <UserPanel currentUser={currentUser} logOut={logOut} />
      <Divider />
      {hasAccessToUserManager && <AdminPanel />}
    </Menu>
  );
};
