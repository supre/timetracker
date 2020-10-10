import React from "react";
import renderer from "react-test-renderer";
import { Entries } from "./../../../components/entries/Entries";

test("render entries", () => {
  let entries = renderer.create(<Entries entries={[]} />);
  let tree = entries.toJSON();
  expect(tree).toMatchSnapshot();
});
