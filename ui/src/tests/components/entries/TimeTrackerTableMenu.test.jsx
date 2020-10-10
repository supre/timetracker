import React from "react";
import renderer from "react-test-renderer";
import { cleanup, fireEvent, render, screen } from "@testing-library/react";
import Menu from "./../../../components/entries/TimeTrackerTableMenu";

const onEvent = (e) => {
  console.log(e);
};

const Component = (props) => {
  const getVal = (key, d) => {
    return props && props[key] ? props[key] : d;
  };

  return (
    <Menu
      userId={1}
      onRefresh={onEvent}
      onDateChange={onEvent}
      onFilterClick={onEvent}
      isFiltered={getVal("isFiltered", false)}
      filterUrl={"http"}
      addEntryToList={onEvent}
      entries={[]}
      currentUser={{ id: 1 }}
    />
  );
};

test("Menu should render", () => {
  let menu = renderer.create(Component());
  let tree = menu.toJSON();
  expect(tree).toMatchSnapshot();
});

test("filtered is only enabled when flag is on", () => {
  render(Component());

  let container = screen.getByText("HTML");
  let parentLink = container.firstChild.parentElement;
  expect(parentLink.classList.contains("disabled")).toBeTruthy();

  cleanup();

  render(Component({ isFiltered: true }));

  container = screen.getByText("HTML");
  parentLink = container.firstChild.parentElement;

  expect(parentLink.classList.contains("disabled")).toBeFalsy();
});

test("Track time click should open entry model", () => {
  render(Component());

  let container = screen.getByText("Track Time");
  fireEvent.click(container);

  let modelContainer = screen.getByText("Track your time").parentElement
    .parentElement;

  expect(modelContainer).toMatchSnapshot();
});
