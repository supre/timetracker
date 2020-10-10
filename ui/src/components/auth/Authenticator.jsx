import { Component } from "react";
import * as storage from "./../../modules/Storage";
import { isTokenValid } from "./../../modules/JwtExtractor";
import { setUser, logOut } from "./../../store/Actions";
import { withRouter } from "react-router-dom";
import { connect } from "react-redux";

class Authenticator extends Component {
  componentDidMount = () => {
    const token = storage.getAccessToken();
    const user = storage.fetchUser();
    const { history, setUser } = this.props;

    if (user && token && isTokenValid(token)) {
      setUser(user);
    } else {
      user && token && storage.clearUser() && logOut();
      setUser(null);
      history.push("/login");
    }
  };

  componentDidUpdate = () => {
    const token = storage.getAccessToken();
    const user = storage.fetchUser();
    const { history, setUser } = this.props;

    if (user && token && !isTokenValid(token)) {
      storage.clearUser() && logOut();
      setUser(null);
      history.push("/login");
    }
  };

  render() {
    const { children } = this.props;
    return children;
  }
}

export default withRouter(
  connect(
    (state) => ({
      isUserLoaded: state.user.isLoaded,
      currentUser: state.user.currentUser,
    }),
    {
      logOut,
      setUser,
    }
  )(Authenticator)
);
