import React, { Component } from "react";
import {
  Grid,
  Form,
  Segment,
  Button,
  Header,
  Message,
  Icon,
} from "semantic-ui-react";
import { Link } from "react-router-dom";
import * as validate from "./../../modules/Validator";
import ServiceClient from "../../modules/ServiceClient";
import { setSuccessMessage } from "./../../store/Actions";
import { connect } from "react-redux";

class Register extends Component {
  errors = [];

  state = {
    team: "",
    displayName: "",
    email: "",
    password: "",
    passwordConfirmation: "",
    errors: [],
    loading: false,
    serviceClient: new ServiceClient(),
  };

  handleChange = (e) => {
    this.setState({
      [e.target.name]: e.target.value,
    });
  };

  handleSubmit = (e) => {
    e.preventDefault();

    const { serviceClient, team, displayName, email, password } = this.state;
    const { history, setSuccessMessage } = this.props;

    this.errors = [];
    this.setState({ loading: true });

    if (this.isFormValid()) {
      serviceClient
        .createTeamAccount(team, displayName, password, email)
        .then((user) => {
          this.setState({ loading: false });
          setSuccessMessage("Successfully registered");
          history.push("/login");
        })
        .catch((err) => {
          this.errors = [...this.errors, ...err];
          this.setState({ errors: this.errors, loading: false });
        });
    } else {
      this.setState({ loading: false });
    }

    this.setState({ errors: this.errors });
  };

  isFormValid = () => {
    return !this.isFormEmpty() && this.isEmailValid() && this.isPasswordValid();
  };

  isEmailValid = () => {
    if (validate.email(this.state.email)) {
      return true;
    }

    this.errors.push("Incorrect email address");
    return false;
  };

  isPasswordValid = () => {
    const { password, passwordConfirmation } = this.state;

    if (
      password.length > 6 &&
      passwordConfirmation.length > 6 &&
      password === passwordConfirmation
    ) {
      return true;
    }

    this.errors.push("Invalid Password");
    return false;
  };

  isFormEmpty = () => {
    const {
      team,
      displayName,
      email,
      password,
      passwordConfirmation,
    } = this.state;

    if (
      team.length > 0 &&
      displayName.length > 0 &&
      email.length > 0 &&
      password.length > 0 &&
      passwordConfirmation.length > 0
    ) {
      return false;
    }

    this.errors.push("Form can't be empty");
    return true;
  };

  saveUser = ({ uid, displayName, photoURL }) => {
    return this.state.ref.child(uid).set({
      displayName: displayName,
      avatar: photoURL,
    });
  };

  render() {
    const {
      team,
      displayName,
      email,
      password,
      passwordConfirmation,
      errors,
      loading,
    } = this.state;
    const RenderError = ({ error }) => {
      return (
        <Message error>
          <Message.Header>{error}</Message.Header>
        </Message>
      );
    };

    return (
      <Grid textAlign="center" verticalAlign="middle" className="app">
        <Grid.Column style={{ maxWidth: "450px" }}>
          <Header as="h1" icon color="orange" textAlign="center">
            <Icon name="clock outline" color="orange" />
            Register for Roar Tracker
          </Header>

          <Form size="large" onSubmit={this.handleSubmit}>
            <Segment stacked>
              <Form.Input
                fluid
                name="team"
                icon="globe"
                iconPosition="left"
                placeholder="Team"
                onChange={this.handleChange}
                type="text"
                value={team}
              />

              <Form.Input
                fluid
                name="displayName"
                icon="user"
                iconPosition="left"
                placeholder="Display Name"
                onChange={this.handleChange}
                type="text"
                value={displayName}
              />

              <Form.Input
                fluid
                name="email"
                icon="mail"
                iconPosition="left"
                placeholder="Email Address"
                onChange={this.handleChange}
                type="text"
                value={email}
              />

              <Form.Input
                fluid
                name="password"
                icon="lock"
                iconPosition="left"
                placeholder="Password"
                onChange={this.handleChange}
                type="password"
                value={password}
              />

              <Form.Input
                fluid
                name="passwordConfirmation"
                icon="repeat"
                iconPosition="left"
                placeholder="Confirm Password"
                onChange={this.handleChange}
                type="password"
                value={passwordConfirmation}
              />

              <Button
                color="orange"
                fluid
                size="large"
                className={loading ? "loading" : ""}
                disabled={loading}
              >
                Submit
              </Button>
            </Segment>
          </Form>

          {errors.map((e, i) => (
            <RenderError key={i} error={e} />
          ))}

          <Message>
            Already a user? <Link to="/login">Sign in</Link>
          </Message>
        </Grid.Column>
      </Grid>
    );
  }
}

export default connect((state) => ({}), {
  setSuccessMessage,
})(Register);
