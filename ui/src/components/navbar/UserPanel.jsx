import React from "react";
import { withRouter, NavLink } from "react-router-dom";

import { Grid, Header, Icon, Dropdown } from "semantic-ui-react";

import * as storage from "./../../modules/Storage";
import EditProfile from "./EditProfile";
import ChangePassword from "./ChangePassword";
import { useState } from "react";

const UserPanel = ({ currentUser, logOut, history }) => {
  const { displayName, team, id } = currentUser;
  const [editModalState, setEditModalState] = useState(false);
  const [passwordModalState, setPasswordModalState] = useState(false);

  const SignedInText = () => (
    <span>
      Signed in as <strong>{displayName}</strong>
    </span>
  );
  const Profile = () => {
    return (
      <span onClick={() => setEditModalState(true)}>
        <Icon name="edit" size="tiny" />
        Edit Profile
      </span>
    );
  };

  const Password = () => {
    return (
      <span onClick={() => setPasswordModalState(true)}>
        <Icon name="lock" size="tiny" />
        Change Password
      </span>
    );
  };

  const SignOut = () => {
    const handleSignout = () => {
      logOut();
      storage.clearUser();
      history.push("/login");
    };

    return <span onClick={handleSignout}>Sign Out</span>;
  };

  const dropdownOptions = [
    {
      key: "user",
      text: <SignedInText />,
      disabled: true,
    },
    {
      key: "profile",
      text: <Profile />,
    },
    {
      key: "changePassword",
      text: <Password />,
    },
    {
      key: "signout",
      text: <SignOut />,
    },
  ];

  const ShowDisplayName = () => <span>{displayName}</span>;

  return (
    <Grid>
      <Grid.Column>
        <Grid.Row style={{ padding: "1.2em", margin: 0 }}>
          <Header inverted floated="left" as="h2">
            <NavLink to="/" style={{ color: "#eee" }}>
              <center>
                <Icon name="clock" />
              </center>
              <Header.Subheader>Roar Tracker</Header.Subheader>
            </NavLink>
          </Header>
          <Header style={{ padding: "0.25em" }} as="h4" inverted>
            <Dropdown trigger={<ShowDisplayName />} options={dropdownOptions} />
          </Header>
        </Grid.Row>
        <EditProfile
          open={editModalState}
          onModalClose={() => setEditModalState(false)}
        />
        <ChangePassword
          open={passwordModalState}
          onModalClose={() => setPasswordModalState(false)}
          team={team}
          userId={id}
        />
      </Grid.Column>
    </Grid>
  );
};

export default withRouter(UserPanel);
