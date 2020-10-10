import React from "react";
import { Icon, Modal, Form, Input, Button } from "semantic-ui-react";
import { useState } from "react";
import ModalErrors from "../common/ModalErrors";
import ServiceClient from "../../modules/ServiceClient";

export default ({ team, userId, open, onModalClose }) => {
  const [errors, setErrors] = useState([]);
  const [filters, setFilters] = useState({
    password: "",
    confirmPassword: "",
  });
  const [loading, setLoading] = useState(false);

  const setFilter = (key, value) => {
    setFilters({ ...filters, [key]: value });
  };

  const formIsNotEmpty = () => {
    const { password, confirmPassword } = filters;

    if (password.length === 0 || confirmPassword.length === 0) {
      setErrors(["All fields are mandatory"]);
      return false;
    }

    return true;
  };

  const validatePassword = () => {
    const { password, confirmPassword } = filters;

    if (password !== confirmPassword) {
      setErrors(["Password doesnt match confirm password."]);
      return false;
    }

    return true;
  };

  const validateForm = () => {
    return formIsNotEmpty() && validatePassword();
  };

  const handleSubmit = () => {
    setErrors([]);
    if (!validateForm()) return;

    setLoading(true);
    const { password } = filters;
    const serviceClient = new ServiceClient();
    serviceClient
      .changePassword(team, userId, password)
      .then((user) => {
        setLoading(false);
        onClose();
      })
      .catch((err) => {
        try {
          setErrors([err]);
        } catch (e) {
          console.log(e);
          setErrors(["Something went wrong"]);
        }

        setLoading(false);
      });
  };

  const onClose = () => {
    setFilters({
      password: "",
      confirmPassword: "",
    });

    setErrors([]);
    onModalClose();
  };

  return (
    <Modal open={open} onClose={onClose} basic closeOnDimmerClick={false}>
      <Modal.Header>
        <Icon name="lock" bordered />
        Change Password
      </Modal.Header>
      <Modal.Content>
        <Form onSubmit={handleSubmit}>
          <Form.Field>
            <Input
              disabled={loading}
              fluid
              label="Password"
              name="password"
              value={filters.password}
              type="password"
              onChange={(e) => setFilter("password", e.target.value)}
            />
          </Form.Field>
          <Form.Field>
            <Input
              disabled={loading}
              fluid
              label="Confirm Password"
              name="confirmPassword"
              value={filters.confirmPassword}
              type="password"
              onChange={(e) => setFilter("confirmPassword", e.target.value)}
            />
          </Form.Field>
        </Form>
      </Modal.Content>
      <ModalErrors errors={errors} />

      <Modal.Actions>
        <Button
          color="green"
          onClick={handleSubmit}
          loading={loading}
          disabled={loading}
        >
          <Icon name="checkmark" /> Save
        </Button>
        <Button color="red" onClick={onClose} disabled={loading}>
          <Icon name="remove" /> Cancel
        </Button>
      </Modal.Actions>
    </Modal>
  );
};
