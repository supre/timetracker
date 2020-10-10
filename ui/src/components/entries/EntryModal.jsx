import React, { useState } from "react";
import {
  Modal,
  Icon,
  Form,
  Input,
  Button,
  TextArea,
  Message,
} from "semantic-ui-react";
import DatePicker from "./DatePicker";
import { formatDateToAtom } from "../../modules/Utilities";
import ServiceClient from "./../../modules/ServiceClient";

export default ({
  openModal,
  onCloseModal,
  addEntryToList,
  currentUser,
  userId,
  entries,
  entryId,
  date = "",
  hoursWorked = "",
  notes = "",
}) => {
  const initialFilters = {
    date: date,
    hoursWorked: hoursWorked,
    notes: notes,
  };

  const [filters, setfilters] = useState(initialFilters);
  const [loading, setloading] = useState(false);
  const [id, setId] = useState(entryId);
  const serviceClient = new ServiceClient();
  const [errors, setErrors] = useState([]);

  const handleHoursWorkedValidation = () => {
    const { hoursWorked } = filters;
    if (Number(hoursWorked) > 24) {
      setErrors([
        "You can't work for more than 24 hours. You are not THAT productive. Nice try though.",
      ]);

      return false;
    }

    if (Number(hoursWorked) < 0.1) {
      setErrors([
        "Did you simply forget to fill in worked hours, or were you slacking all day?",
      ]);

      return false;
    }

    return true;
  };

  const emptyFormValidation = () => {
    const { date, hoursWorked, notes } = filters;
    if (date.length === 0 || hoursWorked.length === 0 || notes.length === 0) {
      setErrors(["All fields are mandatory"]);
      return false;
    }

    return true;
  };

  const validateForm = () => {
    return emptyFormValidation() && handleHoursWorkedValidation();
  };

  const onDateChange = (d) => {
    const filteredEntries = entries.filter((e) => e.userId === userId);
    const userEntries =
      filteredEntries.length > 0 ? filteredEntries[0].entries : [];
    const entriesOnDate = userEntries.filter(
      (e) => formatDateToAtom(e.date) === formatDateToAtom(d.value)
    );
    if (entriesOnDate.length > 0) {
      const entry = entriesOnDate[0];
      const { id, date, hoursWorked, notes } = entry;
      setfilters({
        date: date,
        hoursWorked: hoursWorked,
        notes: notes,
      });
      setId(id);
    } else {
      setFilter("date", d.value);
    }
  };

  const onClose = () => {
    setfilters(initialFilters);
    setId(null);
    setErrors([]);
    onCloseModal();
  };

  const handleSubmit = () => {
    if (!validateForm()) return;
    setloading(true);
    setErrors([]);

    const { date, hoursWorked, notes } = filters;
    const entry = {
      date: date,
      hoursWorked: hoursWorked,
      notes: notes,
      id: id,
    };

    const request = id
      ? serviceClient.updateEntry(currentUser.team, userId, entry)
      : serviceClient.addEntry(currentUser.team, userId, entry);

    request
      .then((receivedEntry) => {
        addEntryToList(userId, receivedEntry);
        setloading(false);
        onClose();
      })
      .catch((err) => {
        try {
          setErrors([...err]);
        } catch (error) {
          console.log(error);
          setErrors("Something went wrong. Try again or contact admin");
        }
        setloading(false);
      });
  };

  const setFilter = (key, value) => {
    setfilters({ ...filters, [key]: value });
  };

  const renderErrors = () => {
    return errors.map((err) => (
      <Message error>
        <Message.Header>{err}</Message.Header>
      </Message>
    ));
  };

  return (
    <Modal open={openModal} onClose={onClose} basic closeOnDimmerClick={false}>
      <Modal.Header>
        <Icon name="clock" />
        Track your time
      </Modal.Header>

      <Modal.Content>
        <Form onSubmit={handleSubmit} className="entryModal">
          <DatePicker
            onDateChange={(d) => onDateChange(d)}
            type="basic"
            placeholder="Pick date"
            iconPosition="left"
            value={filters.date}
            loading={loading}
          />
          <Form.Field>
            <Input
              disabled={loading}
              fluid
              label="HoursWorked"
              name="hoursWorked"
              value={filters.hoursWorked}
              type="text"
              onChange={(e) => setFilter("hoursWorked", e.target.value)}
            />
          </Form.Field>
          <Form.Field>
            <TextArea
              disabled={loading}
              placeholder="Write your notes..."
              name="notes"
              value={filters.notes}
              onChange={(e) => setFilter("notes", e.target.value)}
            />
          </Form.Field>
        </Form>
      </Modal.Content>
      <Modal.Content>{renderErrors()}</Modal.Content>

      <Modal.Actions>
        <Button
          color="green"
          onClick={handleSubmit}
          loading={loading}
          disabled={loading}
        >
          <Icon name="checkmark" /> Add
        </Button>
        <Button color="red" onClick={onClose} disabled={loading}>
          <Icon name="remove" /> Cancel
        </Button>
      </Modal.Actions>
    </Modal>
  );
};
