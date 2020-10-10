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
import ServiceClient from "../../modules/ServiceClient";
import * as validate from "./../../modules/Validator";
import { connect } from "react-redux";
import { setUser, setSuccessMessage } from "../../store/Actions";

class Login extends Component {
  errors = [];

  state = {
    email: "",
    password: "",
    team: "",
    errors: [],
    messages: [this.props.successMessage],
    loading: false,
    serviceClient: new ServiceClient(),
  };

  componentWillUnmount = () => {
    const { setSuccessMessage } = this.props;
    setSuccessMessage(null);
  };

  handleChange = (e) => {
    this.setState({
      [e.target.name]: e.target.value,
    });
  };

  handleSubmit = (e) => {
    e.preventDefault();

    this.errors = [];
    this.setState({ loading: true });

    if (this.isFormValid()) {
      this.state.serviceClient
        .login(this.state.team, this.state.email, this.state.password)
        .then((user) => {
          this.setState({ loading: false, email: "", password: "" });
          this.props.setUser(user);
          this.props.history.push("/");
        })
        .catch((err) => {
          if (Symbol.iterator in Object(err)) {
            this.errors = [...this.errors, ...err];
            this.setState({ errors: this.errors });
            this.setState({ loading: false });
          } else {
            console.log(err);
            this.errors = ["Something went wrong"];
          }
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
    const { password } = this.state;

    if (password.length > 6) {
      return true;
    }

    this.errors.push("Password too short!!");
    return false;
  };

  isFormEmpty = () => {
    const { email, password } = this.state;

    if (email.length > 0 && password.length > 0) {
      return false;
    }

    this.errors.push("Form can't be empty");
    return true;
  };

  RenderMessage = ({ message }) => {
    return message ? (
      <Message positive>
        <Message.Header>{message}</Message.Header>
      </Message>
    ) : (
      ""
    );
  };

  RenderError = ({ error }) => {
    return (
      <Message error>
        <Message.Header>{error}</Message.Header>
      </Message>
    );
  };

  render() {
    const { email, password, errors, messages, loading, team } = this.state;

    return (
      <Grid textAlign="center" verticalAlign="middle" className="app">
        <Grid.Column style={{ maxWidth: "450px" }}>
          <Header as="h1" icon color="violet" textAlign="center">
            <Icon name="clock" color="violet" />
            Login to Roar Tracker
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

              <Button
                color="violet"
                fluid
                size="large"
                className={loading ? "loading" : ""}
                disabled={loading}
              >
                Submit
              </Button>
            </Segment>
          </Form>

          {messages.map((e, i) => (
            <this.RenderMessage key={i} message={e} />
          ))}

          {errors.map((e, i) => (
            <this.RenderError key={i} error={e} />
          ))}

          <Message>
            Don't have an account? <Link to="/register">Sign up</Link>
          </Message>
        </Grid.Column>
      </Grid>
    );
  }
}

export default connect(
  (state) => ({
    currentUser: state.user.currentUser,
    errorMessage: state.auth.errorMessage,
    successMessage: state.auth.successMessage,
  }),
  {
    setUser,
    setSuccessMessage,
  }
)(Login);
