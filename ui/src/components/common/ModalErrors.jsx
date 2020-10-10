import React from "react";
import { Modal, Message } from "semantic-ui-react";

export default ({ errors }) => {
  return errors.length > 0
    ? errors.map((error, key) => {
        return (
          <Modal.Content key={key + 1}>
            <Message error>{error}</Message>
          </Modal.Content>
        );
      })
    : "";
};
