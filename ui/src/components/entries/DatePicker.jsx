import React from "react";
import SemanticDatepicker from "react-semantic-ui-datepickers";
import "react-semantic-ui-datepickers/dist/react-semantic-ui-datepickers.css";
import "./DatePicker.css";

export default ({
  onDateChange,
  placeholder,
  value,
  type = "range",
  pointing = "right",
  iconPosition = "right",
  loading = false,
}) => (
  <SemanticDatepicker
    datePickerOnly
    onChange={(e, d) => onDateChange(d)}
    placeholder={placeholder}
    type={type}
    allowOnlyNumbers
    pointing={pointing}
    iconPosition={iconPosition === "left" ? iconPosition : null}
    maxDate={new Date()}
    value={value}
    loading={loading}
    disabled={loading}
  />
);
