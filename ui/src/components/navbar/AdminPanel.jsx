import React from "react";
import { Menu, Icon } from "semantic-ui-react";
import { NavLink } from "react-router-dom";

export default () => {
  return (
    <Menu.Menu className="menu">
      <Menu.Item>
        <span>
          <Icon name="amilia" /> ADMIN PANEL
        </span>
      </Menu.Item>

      <Menu.Item>
        <Icon name="user" />
        <NavLink to="/users" exact>
          Manage Users
        </NavLink>
      </Menu.Item>
    </Menu.Menu>
  );
};
