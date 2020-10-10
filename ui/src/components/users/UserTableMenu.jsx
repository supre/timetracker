import React, { useState } from "react";
import { Menu, Button } from "semantic-ui-react";
import UserModal from "./UserModal";

export default () => {
  const [openAddUserModelState, setOpenUserModelState] = useState(false);

  return (
    <Menu attached="top" compact secondary inverted>
      <Menu.Menu position="right">
        <Menu.Item>
          <Button
            content="Add User"
            icon="add user"
            labelPosition="right"
            color="facebook"
            inverted
            onClick={() => setOpenUserModelState(true)}
          />

          <UserModal
            onUserModelClose={() => setOpenUserModelState(false)}
            userModalOpenState={openAddUserModelState}
          />
        </Menu.Item>
      </Menu.Menu>
    </Menu>
  );
};
