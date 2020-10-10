import React from "react";
import { Icon, Modal, Form, Input, Button } from "semantic-ui-react";
import { useState } from "react";
import ModalErrors from "../common/ModalErrors";
import { setUser } from "../../store/Actions";
import { connect } from "react-redux";
import ServiceClient from "./../../modules/ServiceClient";
import * as storage from "./../../modules/Storage";
import { useEffect } from "react";
import * as validators from "./../../modules/Validator";

const EditProfile = ({ open, onModalClose, currentUser, setUser }) => {
  useEffect(() => {
    const { id, displayName, email, preferredWorkingHours, team } = currentUser;
    setFilters({
      displayName: displayName,
      email: email,
      preferredWorkingHours: preferredWorkingHours,
      id: id,
      team: team,
    });
  }, [currentUser]);

  const [loading, setloading] = useState(false);
  const [filters, setFilters] = useState({});
  const [errors, setErrors] = useState([]);
  const serviceClient = new ServiceClient();
  const onClose = (e) => {
    setErrors([]);
    onModalClose();
  };

  const formIsFilled = () => {
    const { displayName, email } = filters;

    if (displayName.length === 0 || email.length === 0) {
      setErrors(["All fields are mandatory"]);
      return false;
    }

    return true;
  };

  const preferredWorkingHoursValidation = () => {
    const { preferredWorkingHours } = filters;
    if (Number(preferredWorkingHours) > 24) {
      setErrors([
        "Aimining too high? Lower your preferred working hours to less than 24.",
      ]);
      return false;
    }

    return true;
  };

  const emailIsValid = () => {
    const { email } = filters;
    if (!validators.email(email)) {
      setErrors(["Invalid email"]);
      return false;
    }

    return true;
  };

  const validateForm = () => {
    return (
      formIsFilled() && preferredWorkingHoursValidation() && emailIsValid()
    );
  };

  const handleSubmit = () => {
    setErrors([]);
    if (!validateForm()) return;

    setloading(true);
    const { displayName, email, preferredWorkingHours, id, team } = filters;
    serviceClient
      .updateUser(team, id, displayName, email, null, preferredWorkingHours)
      .then((user) => {
        setUser(user);
        storage.storeUser(user);
        setloading(false);
        onClose();
      })
      .catch((err) => {
        try {
          setErrors(err);
        } catch (error) {
          console.log(error);
          setErrors(["Something went wrong"]);
        }
        setloading(false);
      });
  };

  const setFilter = (key, value) => {
    setFilters({ ...filters, [key]: value });
  };

  return (
    <Modal open={open} onClose={onClose} basic closeOnDimmerClick={false}>
      <Modal.Header>
        <Icon name="edit" bordered />
        Edit Profile
      </Modal.Header>
      <Modal.Content>
        <Form onSubmit={handleSubmit}>
          <Form.Field>
            <Input
              disabled={loading}
              fluid
              label="Display Name"
              name="displayName"
              value={filters.displayName}
              type="text"
              onChange={(e) => setFilter("displayName", e.target.value)}
            />
          </Form.Field>
          <Form.Field>
            <Input
              disabled={loading}
              fluid
              label="Email"
              name="email"
              value={filters.email}
              type="text"
              onChange={(e) => setFilter("email", e.target.value)}
            />
          </Form.Field>
          <Form.Field>
            <Input
              disabled={loading}
              fluid
              label="Preferred working hours"
              name="preferredWorkingHours"
              value={filters.preferredWorkingHours}
              type="text"
              onChange={(e) =>
                setFilter("preferredWorkingHours", e.target.value)
              }
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

export default connect((state) => ({ currentUser: state.user.currentUser }), {
  setUser,
})(EditProfile);
