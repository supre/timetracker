import React, { Component } from "react";
import { connect } from "react-redux";
import { Grid } from "semantic-ui-react";
import Nav from "./navbar/Nav";
import { logOut } from "./../store/Actions";

class Layout extends Component {
  render() {
    const { isUserLoaded, currentUser, logOut, children } = this.props;

    return !isUserLoaded ? (
      <span>Can't find user</span>
    ) : (
      <Grid columns="equal" className="app">
        <Nav currentUser={currentUser} logOut={logOut} />
        <Grid.Column style={{ marginLeft: 320 }}>{children}</Grid.Column>
      </Grid>
    );
  }
}

export default connect(
  (state) => ({
    isUserLoaded: state.user.isLoaded,
    currentUser: state.user.currentUser,
  }),
  {
    logOut,
  }
)(Layout);
