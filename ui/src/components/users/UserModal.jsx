import React, { Component } from "react";
import {
  Modal,
  Button,
  Form,
  Input,
  Icon,
  Select,
  Message,
} from "semantic-ui-react";
import ServiceClient from "../../modules/ServiceClient";
import { connect } from "react-redux";
import "./UserModel.css";
import { addUserToList, updateUserInList } from "../../store/Actions";
import * as validator from "../../modules/Validator";

class UserModal extends Component {
  constructor(props) {
    super(props);

    let action = this.props.action ? this.props.action : "create";
    let displayName = this.props.displayName ? this.props.displayName : "";
    let id = this.props.id ? this.props.id : 0;
    let email = this.props.email ? this.props.email : "";
    let role = this.props.role ? this.props.role : "";

    this.state = {
      action: action,
      id: id,
      displayName: displayName,
      email: email,
      password: "",
      passwordConfirmation: "",
      role: role,
      errors: [],
      loading: false,
      serviceClient: new ServiceClient(),
    };
  }

  clearState = () => {
    this.setState({
      id: 0,
      displayName: "",
      email: "",
      password: "",
      passwordConfirmation: "",
      role: "",
      errors: [],
      loading: false,
    });
  };

  startLoading = () => {
    this.setState({
      loading: true,
    });
  };

  stopLoading = () => {
    this.setState({
      loading: false,
    });
  };

  creatingNewUser = () => this.state.action === "create";

  formNotEmpty = () => {
    const { displayName, email, role } = this.state;

    if (displayName.length === 0 || email.length === 0 || role.length === 0) {
      this.setState({
        errors: [...this.state.errors, "All fields are mandatory"],
      });

      return false;
    }

    return true;
  };

  passwordIsValid = () => {
    // We don't check for password validation if action is not create i.e. when we are updating user
    if (!this.creatingNewUser()) return true;

    const { password, confirmPassword } = this.state;

    let errors = [];

    if (password.length < 9) {
      errors = [...errors, "Password too short"];
    }

    if (password !== confirmPassword) {
      errors = [...errors, "Password and Confirm Password dont match"];
    }

    if (errors.length > 0) {
      this.setState({
        errors: [...this.state.errors, ...errors],
      });

      return false;
    }

    return true;
  };

  emailIsValid = () => {
    const { email, errors } = this.state;
    if (!validator.email(email)) {
      this.setState({
        errors: [...errors, "Invalid email"],
      });

      return false;
    }

    return true;
  };

  validateForm = () => {
    return this.formNotEmpty() && this.passwordIsValid() && this.emailIsValid();
  };

  handleChange = (e) => {
    this.setState({
      [e.target.name]: e.target.value,
    });
  };

  createUser = () => {
    const { addUserToList } = this.props;

    this.setState(
      {
        errors: [],
      },
      () => {
        if (!this.validateForm()) return;

        this.startLoading();

        const {
          serviceClient,
          displayName,
          email,
          password,
          role,
        } = this.state;
        const { currentUser } = this.props;
        const { team } = currentUser;

        serviceClient
          .addUser(team, displayName, password, email, role)
          .then((user) => {
            addUserToList(user);
            this.stopLoading();
            this.onClose();
          })
          .catch((err) => {
            this.setState({
              errors: [...this.state.errors, ...err],
            });
            console.log(err);
            this.stopLoading();
          });
      }
    );
  };

  updateUser = () => {
    const { updateUserInList } = this.props;

    this.setState(
      {
        errors: [],
      },
      () => {
        if (!this.validateForm()) return;

        this.startLoading();

        const { serviceClient, id, displayName, email, role } = this.state;
        const { currentUser } = this.props;
        const { team } = currentUser;

        serviceClient
          .updateUser(team, id, displayName, email, role)
          .then((user) => {
            updateUserInList(user);
            this.stopLoading();
            this.onClose();
          })
          .catch((err) => {
            this.setState({
              errors: [...this.state.errors, ...err],
            });
            this.stopLoading();
          });
      }
    );
  };

  handleSubmit = (e) => {
    this.creatingNewUser() && this.createUser();
    !this.creatingNewUser() && this.updateUser();
  };

  allowedUserRoles = () => {
    const { currentUser } = this.props;
    const { role } = currentUser;

    const allowedUserRoles = [
      {
        key: "user",
        value: "user",
        text: "User",
      },
    ];

    if (role === "admin") {
      allowedUserRoles.push({
        key: "manager",
        value: "manager",
        text: "Manager",
      });
    }

    return allowedUserRoles;
  };

  getInputFormField = ({ label, name, type = "text" }) => {
    const value = this.state[name];
    const { loading } = this.state;

    return (
      <Form.Field>
        <Input
          disabled={loading}
          fluid
          label={label}
          name={name}
          value={value}
          type={type}
          onChange={(e) =>
            this.setState({
              [name]: e.target.value,
            })
          }
        />
      </Form.Field>
    );
  };

  getRoleSelectFormField = ({ label, name }) => {
    const { loading } = this.state;
    const value = this.state[name];

    let className = "ui fluid labeled input";
    className += loading ? " disabled" : "";
    return (
      <Form.Field>
        <div className={className}>
          <div className="ui label label">{label}</div>
          <Select
            fluid
            defaultValue={value}
            className="attachedSelect"
            options={this.allowedUserRoles()}
            basic
            onChange={(e, selection) => {
              this.setState({
                [name]: selection.value,
              });
            }}
          />
        </div>
      </Form.Field>
    );
  };

  onClose = (e) => {
    const { onUserModelClose } = this.props;

    this.creatingNewUser() && this.clearState();
    onUserModelClose(e);
  };

  render() {
    const { userModalOpenState } = this.props;
    const { errors, loading, displayName } = this.state;

    const renderErrors = () => {
      return errors.map((err) => (
        <Message error>
          <Message.Header>{err}</Message.Header>
        </Message>
      ));
    };

    return (
      <Modal
        open={userModalOpenState}
        onClose={this.onClose}
        basic
        closeOnDimmerClick={false}
      >
        <Modal.Header>
          <Icon name="add user" bordered />
          {this.creatingNewUser() ? "Add new user" : "Update " + displayName}
        </Modal.Header>
        <Modal.Content>
          <Form onSubmit={this.handleSubmit}>
            <this.getInputFormField label="Display Name" name="displayName" />
            <this.getInputFormField label="Email" name="email" />
            {this.creatingNewUser() && (
              <this.getInputFormField
                label="Password"
                name="password"
                type="password"
              />
            )}
            {this.creatingNewUser() && (
              <this.getInputFormField
                label="Confirm Password"
                name="confirmPassword"
                type="password"
              />
            )}
            <this.getRoleSelectFormField label="Pick a role" name="role" />
          </Form>
        </Modal.Content>
        <Modal.Content>{renderErrors()}</Modal.Content>

        <Modal.Actions>
          <Button
            color="green"
            onClick={this.handleSubmit}
            loading={loading}
            disabled={loading}
          >
            <Icon name="checkmark" /> {this.creatingNewUser() ? "Add" : "Save"}
          </Button>
          <Button color="red" onClick={this.onClose} disabled={loading}>
            <Icon name="remove" /> Cancel
          </Button>
        </Modal.Actions>
      </Modal>
    );
  }
}

export default connect((state) => ({ currentUser: state.user.currentUser }), {
  addUserToList,
  updateUserInList,
})(UserModal);
