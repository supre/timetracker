import React, { useState } from "react";
import { Table, Button, Modal, Message } from "semantic-ui-react";
import EntryModal from "./EntryModal";
import ServiceClient from "./../../modules/ServiceClient";

export default ({
  userId,
  entries,
  addEntryToList,
  deleteEntryFromList,
  currentUser,
}) => {
  const [errors, setErrors] = useState([]);

  const parseDate = (dateInIsoFormat) => {
    const date = new Date(dateInIsoFormat);
    return date.toDateString();
  };

  const DeleteModal = ({ entry, openModal, onCloseModal }) => {
    const [loading, setLoading] = useState(false);
    const serviceClient = new ServiceClient();

    const deleteEntry = (e) => {
      setLoading(true);
      serviceClient
        .deleteEntry(currentUser.team, userId, entry)
        .then(() => {
          deleteEntryFromList(userId, entry);
          setLoading(false);
          onCloseModal();
        })
        .catch((err) => {
          try {
            setErrors(err);
          } catch (e) {
            console.log(e);
            setErrors(["Something went wrong, try again!!!"]);
          }
          setLoading(false);
        });
    };

    return (
      <Modal open={openModal} onClose={onCloseModal}>
        <Modal.Header>Remove an entry</Modal.Header>
        <Modal.Content>
          <p>Are you sure you want to delete this entry</p>
        </Modal.Content>
        <Modal.Content>
          {errors.map((error) => (
            <Message error>{error}</Message>
          ))}
        </Modal.Content>
        <Modal.Actions>
          <Button
            loading={loading}
            disabled={loading}
            positive
            icon="checkmark"
            labelPosition="right"
            content="Yes"
            onClick={deleteEntry}
          />
        </Modal.Actions>
      </Modal>
    );
  };

  const TableEntry = ({ entry }) => {
    const [isDeleteModalOpen, setDeleteModalState] = useState(false);
    const [trackModelOpen, settrackModelOpen] = useState(false);
    const { id, date, hoursWorked, notes } = entry;
    const didUserWorkMore =
      Number(entry.hoursWorked) >= Number(currentUser.preferredWorkingHours);

    return (
      <Table.Row negative={!didUserWorkMore} positive={didUserWorkMore}>
        <Table.Cell>{parseDate(entry.date)}</Table.Cell>
        <Table.Cell>{entry.hoursWorked}</Table.Cell>
        <Table.Cell>{entry.notes}</Table.Cell>
        <Table.Cell>
          <EntryModal
            openModal={trackModelOpen}
            onCloseModal={() => settrackModelOpen(false)}
            userId={userId}
            entries={entries}
            addEntryToList={addEntryToList}
            date={date}
            notes={notes}
            hoursWorked={hoursWorked}
            currentUser={currentUser}
            entryId={id}
          />
          <Button
            circular
            icon="edit"
            basic
            onClick={() => settrackModelOpen(true)}
          />
          <DeleteModal
            openModal={isDeleteModalOpen}
            onCloseModal={() => setDeleteModalState(false)}
            entry={entry}
          />
          <Button
            circular
            icon="delete"
            basic
            onClick={() => setDeleteModalState(true)}
          />
        </Table.Cell>
      </Table.Row>
    );
  };

  const loadEntries = () => {
    return entries.map((entry, i) => <TableEntry key={i + 1} entry={entry} />);
  };

  return (
    <Table attached="bottom">
      <Table.Header>
        <Table.Row>
          <Table.HeaderCell width={3}>Date</Table.HeaderCell>
          <Table.HeaderCell width={3}>Hours Worked</Table.HeaderCell>
          <Table.HeaderCell width={8}>Notes</Table.HeaderCell>
          <Table.HeaderCell width={2}>Action</Table.HeaderCell>
        </Table.Row>
      </Table.Header>
      {entries.length > 0 ? (
        <Table.Body>{loadEntries()}</Table.Body>
      ) : (
        <Table.Body>
          <Table.Row>
            <Table.Cell colspan={4} style={{ textAlign: "center" }}>
              <Message>
                You don't have any entries. Start tracking your time now.
              </Message>
            </Table.Cell>
          </Table.Row>
        </Table.Body>
      )}
    </Table>
  );
};
