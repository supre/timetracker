import React, { useState, useEffect } from "react";
import TimeTrackerTableMenu from "./TimeTrackerTableMenu";
import TimeTrackerTableView from "./TimeTrackerTableView";
import { Segment, Message } from "semantic-ui-react";
import { connect } from "react-redux";
import {
  loadEntries,
  addEntryToList,
  deleteEntryFromList,
} from "../../store/Actions";
import { withRouter } from "react-router-dom";
import ServiceClient from "./../../modules/ServiceClient";

export const Entries = ({
  entries,
  loadEntries,
  addEntryToList,
  currentUser,
  match,
  deleteEntryFromList,
}) => {
  const [userLoading, setUserLoading] = useState(true);
  const [entriesLoading, setEntriesLoading] = useState(true);
  const [error, setError] = useState(null);
  const [userId, setUserId] = useState(null);
  const [filter, setFilter] = useState({ before: null, after: null });
  const [isFiltered, setIsFiltered] = useState(false);
  const [isRefresh, setIsRefresh] = useState(false);
  const filteredEntries = entries.filter((entry) => entry.userId === userId)[0];
  const userEntries = filteredEntries && filteredEntries.entries;

  useEffect(() => {
    setUserId((match && match.params && match.params.userId) || currentUser.id);
    setUserLoading(false);
  }, [match, currentUser]);

  useEffect(() => {
    if (!userId) return;
    setError(null);
    const serviceClient = new ServiceClient();
    if (!userEntries || isRefresh) {
      const { before, after } = filter;
      serviceClient
        .getEntries(currentUser.team, userId, before, after)
        .then((entries) => {
          loadEntries(userId, entries);
          setEntriesLoading(false);
          setIsRefresh(false);
          before && after ? setIsFiltered(true) : setIsFiltered(false);
        })
        .catch((err) => {
          setError(err);
          setEntriesLoading(false);
          setIsRefresh(false);
        });
    } else {
      setEntriesLoading(false);
    }
  }, [userId, currentUser, isRefresh, filter]);

  const refreshEntries = () => {
    setEntriesLoading(true);
    setIsRefresh(true);
  };

  const onDateChange = (d) => {
    if (!d.value || d.value.length < 2) {
      setFilter({ before: null, after: null });
    } else {
      setFilter({ after: d.value[0], before: d.value[1] });
    }
  };

  const loadFilteredEntries = () => {
    refreshEntries();
  };

  const downloadUrl = () => {
    if (!isFiltered) return "";

    const { before, after } = filter;
    const url = new ServiceClient().getDownloadUrl(
      currentUser.team,
      userId,
      before,
      after
    );

    return url;
  };

  return (
    <Segment basic loading={userLoading || entriesLoading}>
      <TimeTrackerTableMenu
        onRefresh={refreshEntries}
        onDateChange={onDateChange}
        isFiltered={isFiltered}
        onFilterClick={loadFilteredEntries}
        filterUrl={downloadUrl()}
        currentUser={currentUser}
        entries={entries}
        addEntryToList={addEntryToList}
        userId={userId}
      />
      {error && <Message error>{error}</Message>}
      {!error && !userLoading && !entriesLoading && (
        <TimeTrackerTableView
          userId={userId}
          entries={userEntries ? userEntries : []}
          addEntryToList={addEntryToList}
          deleteEntryFromList={deleteEntryFromList}
          currentUser={currentUser}
        />
      )}
    </Segment>
  );
};

export default withRouter(
  connect(
    (state) => ({
      entries: state.entries,
      currentUser: state.user.currentUser,
    }),
    { loadEntries, addEntryToList, deleteEntryFromList }
  )(Entries)
);
