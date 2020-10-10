import React from "react";
import { Icon, Menu, Button } from "semantic-ui-react";
import DatePicker from "./DatePicker";
import { useState } from "react";
import EntryModal from "./EntryModal";

export default ({
  userId,
  onRefresh,
  onDateChange,
  onFilterClick,
  isFiltered,
  filterUrl,
  addEntryToList,
  entries,
  currentUser,
}) => {
  const onFiltersSet = (d) => {
    onDateChange(d);
  };

  const [trackModelOpen, settrackModelOpen] = useState(false);

  return (
    <Menu attached="top" compact secondary inverted>
      <Menu.Menu position="right">
        <Menu.Item>
          <Button
            icon="refresh"
            color="facebook"
            onClick={onRefresh}
            inverted
            circular
          />
        </Menu.Item>
        <Menu.Item>
          <EntryModal
            openModal={trackModelOpen}
            onCloseModal={() => settrackModelOpen(false)}
            userId={userId}
            entries={entries}
            addEntryToList={addEntryToList}
            currentUser={currentUser}
          />
          <Button
            content="Track Time"
            icon="clock outline"
            labelPosition="right"
            color="facebook"
            onClick={() => settrackModelOpen(true)}
            inverted
          />
        </Menu.Item>
        <Menu.Item>
          <DatePicker placeholder="Select Range" onDateChange={onFiltersSet} />
          <Button
            icon
            attached="right"
            color="facebook"
            onClick={onFilterClick}
          >
            <Icon name="filter" />
          </Button>
        </Menu.Item>
        <Menu.Item>
          <Button
            as="a"
            href={filterUrl}
            icon="download"
            content="HTML"
            labelPosition="left"
            color="facebook"
            target="_BLANK"
            disabled={!isFiltered}
          />
        </Menu.Item>
      </Menu.Menu>
    </Menu>
  );
};
