import React from "react";
import "./App.css";
import { BrowserRouter as Router, Switch, Route } from "react-router-dom";
import Login from "./components/auth/Login";
import Register from "./components/auth/Register";
import NotFound from "./components/NotFound";
import Entries from "./components/entries/Entries";
import "semantic-ui-css/semantic.min.css";
import { Provider } from "react-redux";
import { store } from "./store/Store";
import Layout from "./components/Layout";
import UsersManager from "./components/users/UsersManager";
import Authenticator from "./components/auth/Authenticator";

export default () => (
  <Provider store={store}>
    <Router>
      <Switch>
        <Route path="/" exact>
          <Authenticator>
            <Layout>
              <Entries />
            </Layout>
          </Authenticator>
        </Route>
        <Route path="/users" exact>
          <Authenticator>
            <Layout>
              <UsersManager />
            </Layout>
          </Authenticator>
        </Route>
        <Route path="/users/:userId/entries" exact>
          <Authenticator>
            <Layout>
              <Entries />
            </Layout>
          </Authenticator>
        </Route>
        <Route path="/login" component={Login} exact />
        <Route path="/register" component={Register} exact />
        <Route component={NotFound} />
      </Switch>
    </Router>
  </Provider>
);
