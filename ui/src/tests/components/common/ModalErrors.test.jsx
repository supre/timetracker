import React from "react";
import renderer from "react-test-renderer";
import ModalErrors from "../../../components/common/ModalErrors";

test("Login", () => {
  // When there is atleast one error
  let login = renderer.create(<ModalErrors errors={["Test error"]} />);
  let tree = login.toJSON();
  expect(tree).toMatchSnapshot();

  // When there are no errors
  login = renderer.create(<ModalErrors errors={[]} />);
  tree = login.toJSON();
  expect(tree).toMatchSnapshot();
});
