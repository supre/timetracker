import React from "react";
import renderer from "react-test-renderer";
import { cleanup, fireEvent, render, screen } from "@testing-library/react";
import View from "./../../../components/entries/TimeTrackerTableView";

const Component = (
  <View
    entries={[
      {
        id: 1,
        hoursWorked: 8,
        notes: "Positive row",
        date: new Date("2020-06-22"),
      },
      {
        id: 1,
        hoursWorked: 4,
        notes: "Negative row",
        date: new Date("2020-06-22"),
      },
    ]}
    currentUser={{
      preferredWorkingHours: 6,
    }}
  />
);

test("render entries view", () => {
  let view = renderer.create(Component);
  let tree = view.toJSON();
  expect(tree).toMatchSnapshot();
});

test("entries with more hours worked than preferred hours is positive", () => {
  const { getByText } = render(Component);
  expect(
    getByText("Positive row").parentElement.classList.contains("positive")
  ).toBeTruthy();
});

test("entries with less hours worked than preferred hours is negative", () => {
  const { getByText } = render(Component);
  expect(
    getByText("Negative row").parentElement.classList.contains("negative")
  ).toBeTruthy();
});
